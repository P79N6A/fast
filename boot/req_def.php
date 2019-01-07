<?php
/*
 * 本文件是所有request处理的前置入口，请小心修改此文件。 <br/>用于建立应用运行的环境，主要针对MVC的C，即控制层。 date:2011-1-4 author:alex zengjf(alex_zengjf@sina.com)
 */
// ***初始化代码
if (strpos($_SERVER['PHP_SELF'], '.php/') !== false) { // invalid path for php
	header("Location:" . substr(PHP_SELF, 0, strpos(PHP_SELF, '.php/') + 4) . "\n");
	exit();
}
define('ROOT_PATH', substr(__FILE__, 0, strlen(__FILE__) - 16)); // 应用根目录 rtrim 'boot/req_init.php'
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_PATH); // 设置include path，包含文件可忽略ROOT_PATH部分

define('DS', DIRECTORY_SEPARATOR);
define('FT_COMMON_DIR', 'common');
ini_set("session.cookie_httponly", 1); 


$app_debug = false;
// ***初始化代码
include_once ROOT_PATH . 'lib/plugin/Event.php';
include_once ROOT_PATH . 'lib/plugin/PluginUtil.php';
include_once ROOT_PATH . 'lib/plugin/IPlugin.php';
/**
 * RequestContext用于建立应用上下文，是singleton类，可通过$GLOBALS['context']、CTX()、RequestContext::instance()方法 得到此对象
 */
class RequestContext {
	public $app_script;
	public $app_name;
	public $app_path;
	public $app_lang = 'zh_cn';
	public $from_index = false;
	public $theme = 'default';
	public $action = NULL;
	public $wlog = false; // 是否在网页上显示日志，仅用于调试
	private $wlog_text = '';
	public $app = NULL;
	public $request = NULL;
	public $response = NULL;
	public $lang = array();
	public $lang_loaded = array();
	private $app_script_file = NULL;
	private $objlist = array(); // 已经注册接口对象配置参数列表
	private $renderlist = array(); // 已经注册的ReponseRenderer对象配置参数列表
	private $reqlist = array(); // 已经注册的RequestFilter对象配置参数列表
	private $resplist = array(); // 已经注册的ReponseFilter对象配置参数列表

	/**
	 * 注册工具的接口对象到应用上下文，工具对象通过attach方法注入到request对象中，工具对象必须实现@see IRequestTool接口
	 *
	 * @param string $prop
	 *        	可通过context得到此property的值，last bind方式
	 * @param string $impl_file
	 * @param string $impl_class
	 *        	IRequestTool接口
	 */
	function register_tool($prop, $impl_file, $impl_class = NULL) {
		if (! $prop || ! $impl_file)
			throw new Exception("register [{$prop}] to RequestContext fail,paramter error");
		if ($impl_class == NULL)
			list($impl_class, $ext) = explode('.', basename($impl_file), 2);

		$obj = array(
				'file' => $impl_file,
				'class' => $impl_class,
				'obj' => NULL
		);
		$this->objlist[$prop] = $obj;
	}
	function is_debug() {
		// 只有定义了RUN_MODE常量且RUN_MODE不是生产模式时才进入DEBUG模式
		return defined('RUN_MODE') ? RUN_MODE != 'PRO' : false;
	}
	private function register_filter($type, & $impl_class, & $impl_file, $need_callback = NULL) {
		if (! $impl_class || ! $impl_file) {
			if ($type == 0)
				$hint = 'request filter';
			else if ($type == 1)
				$hint = 'response filter';
			else
				$hint = 'renderer';
			throw new Exception("register {$hint} [ {$impl_class} ] to RequestContext fail,paramter error");
		}
		$obj = array(
				'file' => $impl_file,
				'class' => $impl_class,
				'need' => $need_callback,
				'obj' => NULL
		);
		
		if ($type == 0)
			$this->reqlist[$impl_class] = $obj;
		else if ($type == 1)
			$this->resplist[$impl_class] = $obj;
		else
			$this->renderlist[$impl_class] = $obj;
	}
	function register_request_filter($impl_class, $impl_file, $need_callback = NULL) {
		$this->register_filter(0, $impl_class, $impl_file, $need_callback);
	}
	function register_response_filter($impl_class, $impl_file, $need_callback = NULL) {
		$this->register_filter(1, $impl_class, $impl_file, $need_callback);
	}
	function register_renderer($impl_class, $impl_file, $need_callback = NULL) {
		$this->register_filter(2, $impl_class, $impl_file, $need_callback);
	}
	static function is_in_cli() {
		return isset($GLOBALS['argc']) && $GLOBALS['argc'] > 0;
		// return 0==strncasecmp(PHP_SAPI,'cli',3);
	}
	private function &parse_request() {
		if (isset($GLOBALS['argc']) && $GLOBALS['argc'] > 0) {
			$request = array();
			$argv = $GLOBALS['argv'];
			for($i = 1; $i < $GLOBALS['argc']; $i ++) {
				list($key, $val) = explode('=', $argv[$i], 2);
				$request[$key] = $val;
			}
			return $request;
		} else {
//			foreach ( $_COOKIE as $k => $v ) {
//				if (! isset($_REQUEST[$k]))
//					$_REQUEST[$k] = $v;
//			}
			return $_REQUEST;
		}
	}
	private function parse_params_from_path() {
		if (DS == '\\')
			$this->app_script_file = str_replace('/', DS, $_SERVER['SCRIPT_FILENAME']);
		else
			$this->app_script_file = $_SERVER['SCRIPT_FILENAME'];

		$cnt = strlen(ROOT_PATH);
		$file_path = substr($this->app_script_file, $cnt, strlen($this->app_script_file) - $cnt);
		list($_tmp_app_name, $other) = explode(DS, $file_path, 2);
		if (empty($this->app_name)) {
		    $this->app_name = $_tmp_app_name;
		}
		$cnt = strlen('web' . DS . 'app' . DS);
		$app_child = substr($other, $cnt, strlen($other) - $cnt);
		$other = basename($app_child);
		$this->app_path = substr($app_child, 0, strlen($app_child) - strlen($other));
		if (DS === '\\')
			$this->app_path = str_replace('\\', '/', $this->app_path);
		list($this->app_script, $other) = explode('.', $other, 2);
	}
	private function parse_grp_path(array & $app) {
		$path = $grp = '';
		$this->get_grp_path($app['grp'], $path, $grp);
		$app['grp'] = $grp;
		if ($path == '/')
			$app['path'] = '';
		else
			$app['path'] = $path;
	}
	private function get_grp_path($pathgrp, &$path, &$grp) {
		if (! $pathgrp)
			return;
		$pathgrp = str_replace('\\', '/', $pathgrp);
		if (! defined('RUN_SAFE') || RUN_SAFE)
			$pathgrp = preg_replace('/[^a-z0-9_\/]+/i', '', $pathgrp);
		$rpos = strrpos($pathgrp, '/');
		if ($rpos !== false) {
			$path = substr($pathgrp, 0, ++ $rpos);
			$grp = substr($pathgrp, $rpos, strlen($pathgrp) - $rpos);
		} else
			$grp = $pathgrp;
	}
	function get_path_grp_act($action, &$path, &$grp, &$act) {
		if (! $action)
			return;
		$action = str_replace('\\', '/', $action);
		if (! defined('RUN_SAFE') || RUN_SAFE)
			$action = preg_replace('/[^a-z0-9_\/]+/i', '', $action);
		$path = $grp = NULL;
		$rpos = strrpos($action, '/');
		if ($rpos !== false) {
			$pathgrp = substr($action, 0, $rpos);
			$act = substr($action, $rpos + 1, strlen($action) - $rpos);
			$rpos = strrpos($pathgrp, '/');
			if ($rpos !== false) {
				$path = substr($pathgrp, 0, ++ $rpos);
				$grp = substr($pathgrp, $rpos, strlen($pathgrp) - $rpos);
			} else
				$grp = $pathgrp;
		} else
			$act = $action;
	}
	function get_action($grp, $act = 'do_index', $path = NULL) {
		$action = $grp . '/' . ($act ? $act : 'do_index');
		if ($path)
			$action = str_replace('\\', '/', $path) . $action;
		return preg_replace('/[^a-z0-9_\/]+/i', '', $action);
	}
	function prepare_request_handle() {
		$this->request = & $this->parse_request();
		$this->parse_params_from_path();

		$this->response = array();
		$this->app = array();

		reset($this->request);
		for($i = Count($this->request) - 1; $i >= 0; $i --) {
			list($key, $val) = each($this->request);
			if (strncasecmp($key, 'app_', 4) === 0) {
				unset($this->request[$key]);
				$akey = substr($key, 4, strlen($key) - 4);
				$akey = preg_replace('/[^a-z0-9_]+/i', '', $akey);
				$this->app[$akey] = $val;
			}
		}
		reset($this->request);
		
		// set var default
		if (! isset($this->app['name']))
			$this->app['name'] = $this->app_name; // 默认应用名
		else
			$this->app_name = $this->app['name'];
			// get act, grp,path by parse app_act; app_grp :obsolete
		if (isset($this->app['grp']))
			$this->parse_grp_path($this->app);
		if (isset($this->app['act'])) {
			$path = $grp = $act = NULL;
			$this->get_path_grp_act($this->app['act'], $path, $grp, $act);
			$this->app['act'] = $act;
			if ($this->from_index) {
				$this->app['grp'] = $grp;
				$this->app['path'] = $path;
			} else {
				if ($grp)
					$this->app['grp'] = $grp;
				if ($path)
					$this->app['path'] = $path;
			}
		}
		require_lang('req', false);
	}
	function fire_request_handle() {
		$app = & $this->app;
		$request = & $this->request;
		$response = & $this->response;

		/* init error handle */
		if ($GLOBALS['app_debug'])
			set_error_handler(array(
					$this,
					'log_php_error_handler'
			)); // ,E_ALL);
		else
			set_error_handler(array(
					$this,
					'log_php_error_handler'
			), E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR); // E_WARNING
		set_exception_handler(array(
				$this,
				'log_php_exception_handler'
		));
		/* end init */

		// set default value
		if (! isset($this->app['act']))
			$this->app['act'] = 'do_index'; // 默认处理函数
		if ($this->from_index) {
			if (isset($this->app['path']))
				$this->app_path = $this->app['path'];
			if (isset($this->app['grp']))
				$this->app_script = $this->app['grp'];

			if (! isset($this->app['grp']) || ! $this->app['grp'])
				$this->app['grp'] = $this->app_script = 'index';
		} else {
			if (RUN_SAFE) { // get grp,path from url path,canot modify by app_act
				$this->app['grp'] = $this->app_script;
				$this->app['path'] = $this->app_path;
			} else {
				if (! isset($this->app['grp']))
					$this->app['grp'] = $this->app_script; // 默认类名
				else
					$this->app_script = $this->app['grp'];

				if (! isset($this->app['path']))
					$this->app['path'] = $this->app_path; // 默认类路径
				else
					$this->app_path = $this->app['path'];
			}
		}
		if (! isset($this->app['title']))
			$this->app['title'] = '';

		$this->action = $this->get_action($this->app_script, $this->app['act'], $this->app_path);

		if (! isset($this->app['fmt']))
			$this->app['fmt'] = 'html'; // 响应格式,默认格式html,other json,csv
                //add wq
                if (defined('APP_MODE_CLI_CHECK')&&APP_MODE_CLI_CHECK===TRUE) {//cli模式不检测权限
                            if(isset($this->app['mode'])&&$this->app['mode'] == 'cli'){
                                unset($this->app['mode']);
                            }
                             if($this->is_in_cli()){
                                 $this->app['mode'] = 'cli';
                             }
		} 
   
		if (RUN_SAFE === true && $this->is_in_cli())
			$this->app['mode'] = 'cli'; // 应用类型:cli,web,func -批处理，web应用，func方法.
		if (! isset($this->app['mode']) || ($this->app['mode'] != 'cli' && $this->app['mode'] != 'func'))
			$this->app['mode'] = 'web'; // 默认类型
		$this->wlog = $this->wlog && defined('RUN_WEB_LOG') && RUN_WEB_LOG && $app['mode'] == 'web' && $app['fmt'] == 'html';

		$app['step'] = 'seq'; // 执行顺序,default seq, 1.seq:squence,2.call:goto call,3.resp:goto response filter,4.rend:goto renderer
		                      // req:goto request filter ,return: end//not mean
		$app['err_no'] = 0;

		if ($this->from_index) {
			$this->app_script_file = get_controller_path($this->app_path, $this->app_script);
			if (! file_exists($this->app_script_file)) {
				$this->log_error("call handle func,file {$this->app_script_file} not found");
				$GLOBALS['context']->put_error(404, lang('req_err_404') . "[{$this->app_path}{$this->app_script}.php]");
			}
			include_once $this->app_script_file;
		}

		// call request filter
		if (! isset($app['step']) || ($app['step'] == 'seq'))
			foreach ( $this->reqlist as $classid => $objitem )
				try {
					if ($objitem['need'] === NULL || $objitem['need']($app)) {
						if (($obj = $objitem['obj']) === NULL) {
							if (file_exists(ROOT_PATH . "/{$this->app_name}/" . $objitem['file'])) {
								include_once ROOT_PATH . "/{$this->app_name}/" . $objitem['file'];
							} else {
								include_once ROOT_PATH . $objitem['file'];
							}
							$objitem['obj'] = $obj = new $objitem['class']();
							if (array_key_exists($classid, $this->resplist))
								$this->resplist[$classid]['obj'] = $obj;
						}
						if ($obj instanceof IRequestFilter)
							if ($obj->handle_before($request, $response, $app) === true)
								break;
					}
				} catch ( Exception $e ) {
					$this->log_error("call request filter [{$classid}] fail," . $e->getMessage());
					$GLOBALS['context']->put_error(500, lang('req_err_500'). $e->getMessage());
				}
		if ($app['step'] == 'return')
			return;

			// call handle func
 
		if (! isset($app['step']) || $app['step'] == 'seq' || $app['step'] == 'call')
			$this->do_step_call($request, $response, $app);
           
		if ($app['step'] == 'return')
			return;

			// call response filter
		if (! isset($app['step']) || $app['step'] == 'seq' || $app['step'] == 'resp')
			foreach ( $this->resplist as $classid => $objitem )
				try {
					if ($objitem['need'] === NULL || $objitem['need']($app)) {
						$obj = $objitem['obj'];
						if (($obj = $objitem['obj']) === NULL) {

							if (file_exists(ROOT_PATH . "/{$this->app_name}/" . $objitem['file'])) {
								include_once ROOT_PATH . "/{$this->app_name}/" . $objitem['file'];
							} else {
								include_once ROOT_PATH . $objitem['file'];
							}

							$objitem['obj'] = $obj = new $objitem['class']();
							if (array_key_exists($classid, $this->reqlist))
								$this->reqlist[$classid]['obj'] = $obj;
						}
						if ($obj instanceof IReponseFilter)
							if ($obj->handle_after($request, $response, $app) === true)
								break;
					}
				} catch ( Exception $e ) {
					$this->log_error("call response filter [{$classid}] fail," . $e->getMessage());
					$GLOBALS['context']->put_error(500, lang('req_err_500'). $e->getMessage());
				}
		if ($app['step'] == 'return')
			return;

			// call render handle
		if (! isset($app['step']) || $app['step'] == 'seq' || $app['step'] == 'rend')
			foreach ( $this->renderlist as $classid => $objitem )
				try {
					if ($objitem['need'] === NULL || $objitem['need']($app)) {
						$obj = $objitem['obj'];
						if (($obj = $objitem['obj']) === NULL) {

							if (file_exists(ROOT_PATH . "/{$this->app_name}/" . $objitem['file'])) {
								include_once ROOT_PATH . "/{$this->app_name}/" . $objitem['file'];
							} else {
								include_once ROOT_PATH . $objitem['file'];
							}

							$objitem['obj'] = $obj = new $objitem['class']();
						}
						if ($obj instanceof IReponseRenderer) {
							if ($obj->render($request, $response, $app) === true)
								break;
						}
					}
				} catch ( Exception $e ) { 
					$this->log_error("call response renderer [{$classid}] fail," . $e->getMessage());
				}
                                     
	}
	static function get_obj_from_grp($grp, $path) {
		$clazz = $grp;
		if (class_exists($clazz))
			return new $clazz(); // not check is action controller class or file
		else if (strcmp($clazz, 'index') === 0 && $path) {
			$len = strlen($path) - 1;
			if ($path[$len] == '/')
				$path = substr($path, 0, $len);
			$clazz = basename($path);
			if ($clazz && class_exists($clazz))
				return new $clazz();
		}
		return NULL;
	}
	private function do_step_call(array & $request, array & $response, array & $app) {

		$class_name = $app['grp'];
		$method_name = $app['act'];
		$callback = NULL;
		$obj = self::get_obj_from_grp($class_name, $this->app_path);
     
		if ($obj) {
			if (method_exists($obj, $method_name)) {
				if (RUN_SAFE) { 
					$func = new ReflectionMethod($class_name, $method_name);
					if (! $func->isPublic() || $func->getFileName() !== str_replace('/', DS, $this->app_script_file) || $func->isInternal() || (PHP_VERSION_ID > 50300 && $func->isClosure()) || $func->isAbstract() || $func->isConstructor()) {
						$this->log_error("forbid handle func {$method_name} in {$func->getFileName()} ");
						$GLOBALS['context']->put_error(403, lang('req_err_403') . ' [' . $method_name . ']');
					}
				}
				$callback = array(
						$obj,
						$method_name
				);
			} else if (method_exists($obj, '__call')) {
				$callback = array(
						$obj,
						$method_name
				);
			}
		} else {
			if (function_exists($method_name)) {
				if (RUN_SAFE) {
					$func = new ReflectionFunction($method_name);
					if ($func->getFileName() !== str_replace('/', DS, $this->app_script_file) || $func->isInternal() || (PHP_VERSION_ID > 50300 && $func->isClosure())) {
						$this->log_error("invalid call handle func {$method_name} in {$func->getFileName()} ");
						$GLOBALS['context']->put_error(403, lang('req_err_403') . ' [' . $method_name . ']');
					}
				}
				$callback = $method_name;
			} else {
				unset($app['act']);
				unset($app['grp']);
			}
		}
		if (! $callback) {
			$this->log_error("call handle func {$class_name}[{$method_name}] fail in {$this->action}");
			$GLOBALS['context']->put_error(501, lang('req_err_501') . "[{$this->action}]");
		}
                
		// call handle func
		// @ $obj->$method_name ( $request ); //isok ?
		if (! isset($app['step']) || $app['step'] == 'seq' || $app['step'] == 'call')
			try {
                               
				$this->_do_call_action($callback, $request, $response, $app);
			} catch ( Exception $e ) {
				$this->log_error("call [{$class_name}->{$method_name}] fail," . $e->getMessage());
				$GLOBALS['context']->put_error(500, lang('req_err_500'). $e->getMessage());
                               
			}
	}
	function do_app_init() {
		$app_init = 'app_init';
		$app_init_file = ROOT_PATH . "/{$this->app_name}/boot/{$app_init}.php";
		if (file_exists($app_init_file)) {
			include_once $app_init_file;
			if (function_exists($app_init))
				$app_init();
		}
		if (!isset($this->app['show_mode'])) {
			$this->app['show_mode'] = '';
		}
		if (!isset($this->app['scene'])) {
			$this->app['scene'] = 'default';
		}
	}
	// magic property for last include file.
	public function get_property($prop) { // last bind property
		$obj = & $this->objlist[$prop];
		if (! isset($obj))
			return NULL;

		if (! isset($obj['obj'])) {
			require_once ROOT_PATH . $obj['file'];

			$class_name = $obj['class'];
			if (class_exists($class_name)) {
				try {
					$tmpobj = call_user_func(array(
							$class_name,
							'register'
					), $prop);
					if (isset($tmpobj) && is_object($tmpobj)) {
						$obj['obj'] = $tmpobj;
						return $tmpobj;
					} else
						$this->log_error("register [{$prop}] to RequestContext fail,register function return null");
				} catch ( Exception $e ) {
					$this->log_error("register [{$prop}] to RequestContext fail,call register function exception " . $e->getMessage());
				}
			}
			return NULL;
		} else
			return $obj['obj'];
	}
	function __get($name) {
		return $this->get_property($name);
	}
	function __isset($name) {
		return isset($this->objlist[$name]);
	}
	function unset_obj($names){
            $name_arr = explode(',', $names);
            foreach($name_arr as $name){
                if(isset($this->objlist[$name]['obj'])){
                    unset($this->objlist[$name]['obj']);
                }
            }
    }
	// error handle
	function log_php_error_handler($errno, $errstr, $errfile, $errline) {
		if ($errno == E_USER_NOTICE) { // module trigger error
			$this->log_error('USER' . lang('req_log_info') . "[{$errno}] : {$errstr},in file {$errfile}[{$errline}]");
			return true;
		} else if ($errno == E_ERROR || $errno == E_USER_ERROR || $errno == E_CORE_ERROR || $errno == E_COMPILE_ERROR || $errno = E_WARNING) {
			$this->log_error('PHP' . lang('req_log_error') . "[{$errno}] : {$errstr},in file {$errfile}[{$errline}]");
		} else if ($errno != E_STRICT) {
			$this->log_debug('PHP' . lang('req_log_debug') . "[{$errno}] : {$errstr},in file {$errfile}[{$errline}]");
		}
		if (DEBUG)
			return false;
	}
	function log_php_exception_handler($e) {
		$this->log_error('PHP' . lang('req_log_except') . "[{$e->getCode()}] : {$e->getMessage()},in file {$e->getFile()}[{$e->getLine()}]");
	}
	private $app_conf = NULL;
	function get_app_conf($_FASTAPP_NAME_a3ap3p4o_8na6me_) {
		if ($this->app_conf === NULL) {
			$_conf_file = ROOT_PATH . $this->app_name . "/boot/app_conf.php";
			if (defined('RUN_MODE')) {
				$_f = ROOT_PATH . $this->app_name . "/boot/app_conf_".strtolower(RUN_MODE).".php";
				if (file_exists($_f)) {
					$_conf_file = $_f;
				}
			}
			include $_conf_file;
			
			$this->app_conf = get_defined_vars();
			unset($this->app_conf['_FASTAPP_NAME_a3ap3p4o_8na6me_']);
			if (! defined('RUN_FAST') || ! RUN_FAST) {
				if (! isset($this->app_conf['base_url']) && isset($_SERVER['PHP_SELF'])) {
					$b = str_replace(array(
							'<',
							'>',
							'*',
							'\'',
							'"'
					), '', $_SERVER['PHP_SELF']);
					$r = strpos($b, '/app/');
					$this->app_conf['base_url'] = $r ? substr($b, 0, $r + 1) : '/';
				}
				if (! isset($this->app_conf['app_url']))
					$this->app_conf['app_url'] = $this->app_conf['base_url'];
			}
		}
		if (isset($this->app_conf[$_FASTAPP_NAME_a3ap3p4o_8na6me_]))
			return $this->app_conf[$_FASTAPP_NAME_a3ap3p4o_8na6me_];
	}
	private $action_list = array();
	function call_action($actions) {
		$action_list = explode(',', $actions);
		foreach ( $action_list as $action ) {
			if (empty($action) || $action == $this->action)
				continue;
			$path = $act = $grp = NULL;
			$this->get_path_grp_act($action, $path, $grp, $act);
			if (! $grp)
				$grp = $this->app_script; // default current app settings
			if (! $path)
				$path = $this->app_path;

			$id = strtolower("{$path}{$grp}");
			if (isset($this->action_list[$id])) {
				$obj = $this->action_list[$id]; // only get controller obj
				if (method_exists($obj, $act)) {
					$this->_do_call_action(array(
							$obj,
							$act
					), $this->request, $this->response, $this->app);
				}
				continue;
			}
			if (! $grp) {
				include_once get_controller_path($path, $grp);
			}
			$obj = self::get_obj_from_grp($grp, $path);
			if ($obj) {
				$this->action_list[$id] = $obj;
				if (method_exists($obj, $act)) {
					$this->_do_call_action(array(
							$obj,
							$act
					), $this->request, $this->response, $this->app);
				}
			} else if (function_exists($act)) {
				$this->_do_call_action($act, $this->request, $this->response, $this->app);
			}
		}
	}
	private function _do_call_action($callback, array &$request, array &$response, array &$app) {
		Event::fire('beforeCallAction', array(
				$request,
				$response,
				$app
		));
                
		if (is_array($callback)) {
			$result = $callback[0]->$callback[1]($request, $response, $app);
		} else {
			$result = $callback($request, $response, $app);
		}
            
		Event::fire('afterCallAction', array(
				$request,
				$response,
				$app
		));
	}
	private $sess_init = false;
	function init_session() {
		if ($this->sess_init)
			return;

                $cookie_domain = NULL;
                ini_set('session.gc_maxlifetime', 3600); //默认1个小时过期
		if (defined('RUN_SESSPUB') && RUN_SESSPUB) {
			session_name('fastappsid');
			$cookie_path = '/';
			/* 这里会读数据库，改成如果配置文件没有指定数据库的情况下，直接给个默认值*/
			$conf_db_host = ctx()->get_app_conf('db_host');
			if (empty($conf_db_host)){
				$cookie_lifetime = 3600;
			}else{
				$cookie_lifetime = $this->conf->get('cookie.alive', 3600) + 5;				
			}
                     //  session_set_cookie_params($cookie_lifetime, $cookie_path,null,FALSE,true);
                        
                        $cookie_domain = $this->conf->get('cookie.domain', '');
                        if ($cookie_domain){
                            session_set_cookie_params($cookie_lifetime,$cookie_path,$cookie_domain,true,true);
                        }else{
                            session_set_cookie_params($cookie_lifetime, $cookie_path,null,true,true);
                        }
                   
                        
                        
		} else {
			session_name($this->app_name . 'sid');
			if ($this->get_app_conf('base_url'))
				$cookie_path = $this->get_app_conf('base_url');
			else
				$cookie_path = $this->conf->get('cookie.path', '/');
			$cookie_lifetime = $this->conf->get('cookie.alive', 3600) + 5;
			$cookie_domain = $this->conf->get('cookie.domain', '');
			if ($cookie_domain)
				session_set_cookie_params($cookie_lifetime, $cookie_path,$cookie_domain,false,true);
			else
				session_set_cookie_params($cookie_lifetime, $cookie_path, $cookie_domain,false,true); // server time must ok
		}
                 
		require_lib('session/SessionManager');
		new SessionManager();
		

                session_start(); 
		setcookie(session_name(), session_id(), time() + $cookie_lifetime, $cookie_path,$cookie_domain,false,true);
		$this->sess_init = true;
	}
	function get_session($name, $pub = true) {
         
		$this->init_session();
		if (! $pub)
			$name = 'fAp' . $this->app_name . $name;
		return isset($_SESSION[$name]) ? $_SESSION[$name] : NULL;
	}
	function set_session($name, $value, $pub = true) {
		$this->init_session();
		if (! $pub)
			$name = 'fAp' . $this->app_name . $name;
		$_SESSION[$name] = $value;
	}
	function set_cookie($name, $value, $ttl = NULL, $pub = false) { // see:del_cookie
		if ($pub) {
			$cookie_path = '/';
			$cookie_domain = false;
		} else {
			if ($this->get_app_conf('base_url'))
				$cookie_path = $this->get_app_conf('base_url');
			else
				$cookie_path = $this->get_app_conf('cookie_path'); // 如果$pub=true则必须设置 $cookie_path='/opm/';

			$cookie_domain = $this->conf->get('cookie.domain', '');
		}
		if ($ttl === NULL)
			$ttl = $this->conf->get('cookie.alive', 1800) + time() + 5;
		else
			$ttl = $ttl + time();
		if ($cookie_domain)
			setcookie($name, $value, $ttl, $cookie_path, $cookie_domain,false,true);
		else
			setcookie($name, $value, $ttl, $cookie_path,null,false,true);
	}
	function forward($action) {
		if (empty($action) || $action == $this->action)
			return;
		$path = $grp = $act = NULL;
		$this->get_path_grp_act($action, $path, $grp, $act);
		$this->app['act'] = $act;
		if ($grp) {
			$this->app_script = $this->app['grp'] = $grp; // default current settings;
			$this->app_path = $this->app['path'] = $path;
			include_once get_controller_path($this->app_path, $this->app_script);
		}
		$this->action = $action;
		$this->do_step_call($this->request, $this->response, $this->app);
	}
	function redirect($action, $options = NULL, $relay = 0, $relay_msg = '') {
		$url = get_app_url($action, $options);
		if (! headers_sent()) {
			if ($relay > 0) {
				header('Content-Type: text/html;charset=' . $this->get_app_conf('charset'));
				header("Refresh:{$relay};url={$url}");
				if ($relay_msg)
					echo $relay_msg;
				else
					echo lang('req_redirect_msg');
			} else
				header("Location:{$url}");
			exit();
		} else {
			$reply = "<meta http-equiv='Refresh' content='{$relay};url={$url}'>";
			if ($relay > 0)
				$reply .= $relay_msg;
			exit($reply);
		}
	}
	// 优化应用性能，请主要使用下面log方法
	private $_log;
	function get_trace_file($backtrace) {
		static $ingnore_files = array(
				'PDODB.class.php',
				'TableMapper.class.php',
				'req_init.php',
				'base_model.php'
		);
		$sp = 0;
		$trace = "";
		foreach ( $backtrace as $k => $v ) {
			extract($v);
			$_file = basename($file);
			if (in_array($_file, $ingnore_files))
				continue; // the db object

			if (isset($backtrace[$k + 1])) {
				$trace = "($file:$line:" . $backtrace[$k + 1]['function'] . ")";
			}
			break;
		}

		return $trace;
	}
	function log_error($msg) {
		if (! isset($this->_log))
			$this->_log = $this->get_property('log');
		$trace = debug_backtrace();
		$msg = $this->get_trace_file($trace) . "\t" . $msg;

		$this->_log->error($msg);
		if ($this->wlog && $GLOBALS['app_debug'])
			$this->wlog_text .= $msg . "\n";
	}
	function log_debug($msg) {
		if (! $GLOBALS['app_debug'])
			return;
		if (! isset($this->_log))
			$this->_log = $this->get_property('log');
		if (DEBUG === true) {
			$trace = debug_backtrace();
			$msg = $this->get_trace_file($trace) . "\t" . $msg;
		}

		$this->_log->debug($msg);
		if ($this->wlog)
			$this->wlog_text .= $msg . "\n";
	}
	function put_wlog() {
		if ($this->wlog && $GLOBALS['app_debug'])
			echo "\n<br/><b>web log:</b><pre>" . $this->wlog_text . "</pre>\n";
	}
	function put_error($errno, $errmsg) {
		if (! $this->app['err_no'])
			$this->app['err_no'] = $errno;
		if (isset($this->app['err_msg']))
			$this->app['err_msg'] .= " ; " . $errmsg;
		else
			$this->app['err_msg'] = $errmsg;
		$this->app['step'] = 'rend';
		$this->log_error(lang('req_err_title') . "[{$errno}],{$errmsg}");
	}
	// 以下实现singleton
	private static $context;
	private function __construct() {
		$this->from_index = defined('RUN_FROM_INDEX') && RUN_FROM_INDEX;
		if (self::$context)
			throw new Exception("RequestContext CANNOT create! use 'instance' method");
	}
	public static function instance() {
		if (! isset(self::$context))
			self::$context = new RequestContext();
		return self::$context;
	}
}
// public function
function require_lib($clazzs, $first_app = true, $base_path = null) {
    global $context;
    $clazzlist = explode(',', $clazzs);
    $clazz_files = array();
    foreach ($clazzlist as $clazz) {
        $clazz = trim($clazz);
        if (isset($base_path)) {
            $clazz_file = ROOT_PATH . $base_path . "/lib/{$clazz}.php";
        } else {
            $common_dir = FT_COMMON_DIR;
            if ($first_app) {
                $clazz_file = ROOT_PATH . $context->app_name . "/lib/{$clazz}.php";
                if (!empty($common_dir) && !file_exists($clazz_file)) {
                    $clazz_file = ROOT_PATH . $common_dir . "/lib/{$clazz}.php";
                }
                if (!file_exists($clazz_file)) {
                    $clazz_file = ROOT_PATH . "lib/{$clazz}.php";
                }
            } else {
                $clazz_file = ROOT_PATH . "lib/{$clazz}.php";
                if (!file_exists($clazz_file)) {
                    $clazz_file = ROOT_PATH . $context->app_name . "/lib/{$clazz}.php";
                }
            }
        }
        $clazz_files[] = $clazz_file;
    }
    foreach ($clazz_files as $clazz_file) {
        if (!file_exists($clazz_file)) {
            $context->log_error('load_lib fail,[' . $clazz_file . '] not found');
            return false;
        } else {

            require_once $clazz_file;
        }
    }

    return true;
}

function require_model($clazzs, $first_app = true, $base_path = null) {
    $ret = true;
    global $context;
    $clazzlist = explode(',', $clazzs);
    foreach ($clazzlist as $clazz) {
        $clazz = trim($clazz);
        if (isset($base_path)) {
            $clazz_file = ROOT_PATH . $base_path . "/models/{$clazz}.php";
        } else {
            $clazz_file = ROOT_PATH . $context->app_name . "/models/{$clazz}.php";

            $common_dir = FT_COMMON_DIR;
            if (!empty($common_dir) && !file_exists($clazz_file)) {
           
                $clazz_file = ROOT_PATH . $common_dir . "/models/{$clazz}.php";
            }
        }
      
        //echo '<hr/>$base_path<xmp>'.var_export($base_path,true).'</xmp>';
        //echo '<hr/>$clazz_file<xmp>'.var_export($clazz_file,true).'</xmp>';
        if (!file_exists($clazz_file)) {
            $context->log_error('load_model fail,[' . $clazz_file . '] not found');
            $ret = false;
        } else {
            require_once $clazz_file;
        }
    }
    
    return $ret;
}

function require_lang($pkgs, $first_app = true) {
	global $context;
	$pkgs = explode(',', $pkgs);
	$clazz_files = array();
	foreach ( $pkgs as $pkg ) {
		$pkg = trim($pkg);
		if (in_array($pkg, $context->lang_loaded, true))
			continue;
		if ($first_app) {
			$clazz_file = ROOT_PATH . "{$context->app_name}/lang/{$context->app_lang}/{$pkg}.php";
			if (! file_exists($clazz_file))
				$clazz_file = ROOT_PATH . "lib/lang/{$context->app_lang}/{$pkg}.php";
		} else {
			$clazz_file = ROOT_PATH . "lib/lang/{$context->app_lang}/{$pkg}.php";
			if (! file_exists($clazz_file))
				$clazz_file = ROOT_PATH . "{$context->app_name}/lang/{$context->app_lang}/{$pkg}.php";
		}
		$clazz_files[] = $clazz_file;

        if (file_exists($clazz_file)) {
            include $clazz_file;
            $context->lang = array_merge($context->lang, $$pkg);
            $context->lang_loaded[] = $pkg;
        } else
            $context->log_error("require_lang fail,language file [{$clazz_file}] NOT FOUND");
	}

}
function lang($key) {
	global $context;
	if (isset($context->lang[$key]))
		return $context->lang[$key];
	else {
                //语言错误屏蔽
		//$context->log_error("language key [{$key}] NOT FOUND");
                
		return $key;
	}
}
function get_controller_path($grp, $name) {
	global $context;
	if (! empty($grp)) {
		$ft = ROOT_PATH . "%s" . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $grp . DIRECTORY_SEPARATOR . $name . '.php';
		$f = sprintf($ft, $context->app_name);
		if (!file_exists($f)) {
			$f = sprintf($ft, FT_COMMON_DIR);
		}
	} else {
		$ft = ROOT_PATH . "%s" . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $name . '.php';
		$f = sprintf($ft, $context->app_name);
		if (!file_exists($f)) {
			$f = sprintf($ft, FT_COMMON_DIR);
		}
	}
	CTX()->log_debug($f);
	return $f;
}
function get_tpl_path($tplname) {
	global $context;
	$views_path = ROOT_PATH . $context->app_name . DS . 'views' . DS;
	if ($context->theme)
		$views_path .= $context->theme . DS;
	if (defined('RUN_MLANG_VIEW') && RUN_MLANG_VIEW)
		$views_path .= $context->app_lang . DS;
	if ('/' !== DS)
		$tplname = str_replace('/', DS, $tplname);

	$tplpage = $views_path . $tplname . '.tpl.php';

	$common_dir = FT_COMMON_DIR;
	if (! empty($common_dir) && ! file_exists($tplpage)) {
		$views_path = ROOT_PATH . $common_dir . DS . 'views' . DS;
		if ($context->theme)
			$views_path .= $context->theme . DS;
		if (defined('RUN_MLANG_VIEW') && RUN_MLANG_VIEW)
			$views_path .= $context->app_lang . DS;
		if ('/' !== DS)
			$tplname = str_replace('/', DS, $tplname);

		$tplpage = $views_path . $tplname . '.tpl.php';
	}

	return $tplpage;
}
function get_url($url_part, $is_pub = false) {
	$url_part = trim($url_part);
	if ($is_pub)
		return $GLOBALS['context']->get_app_conf('pub_url') . $url_part;
	else
		return $GLOBALS['context']->get_app_conf('base_url') . $url_part;
}
function echo_url($url_part, $is_pub = false) {
	echo get_url($url_part, $is_pub);
}
function get_theme_url($url_part, $mutil_lang = true) {
	$pre_http_path = CTX()->get_app_conf('common_http_url');
	$url = $pre_http_path . "theme/{$GLOBALS['context']->theme}/";
	if ($mutil_lang && defined('RUN_MLANG_VIEW') && RUN_MLANG_VIEW)
		$url .= $context->app_lang . '/';

	return $url . trim($url_part);
}
function echo_theme_url($url_part, $mutil_lang = true) {
	echo get_theme_url($url_part, $mutil_lang);
}
function get_app_url($action, $options = NULL, $is_control = false) {
    if (substr($action, 0, 7) == 'http://') {
    	return $action;
    }
   if (substr($action, 0, 8) == 'https://') {
    	return $action;
    }
	$ctx = $GLOBALS['context'];
	if ($is_control) {
		if (! defined('RUN_SAFE') || RUN_SAFE)
			$action = preg_replace('/[^a-z0-9_\/]+/i', '', $action);
		if ($ctx->from_index)
			$loc = $ctx->get_app_conf('base_url') . "?app_act=ctl/index/do_index&app_ctl={$action}";
		else
			$loc = $ctx->get_app_conf('base_url') . "app/ctl/?app_ctl={$action}";
	} else {
		if ($ctx->from_index) {
			if (! defined('RUN_SAFE') || RUN_SAFE)
				$action = preg_replace('/[^a-z0-9_\/]+/i', '', $action);
			if (!empty($action) && $action[strlen($action) - 1] === '/')
				$action .= 'do_index';
			$loc = $ctx->get_app_conf('base_url') . "?app_act={$action}";
		} else {
			$path = $grp = $act = NULL;
			if ($action[strlen($action) - 1] === '/')
				$action .= 'do_index';
			$ctx->get_path_grp_act($action, $path, $grp, $act);
			if (! $act)
				$ctx->put_error(1500, "function get_app_url error:grp not found,action param is " . $action);
			if (! $grp) {
				$grp = $ctx->app_script;
				$path = $ctx->app_path;
			}
			$loc = $ctx->get_app_conf('base_url') . "app/{$path}{$grp}." . $ctx->get_app_conf('php_ext');
			if ($act)
				$loc .= "?app_act={$act}";
		}
	}
	if ($options && is_array($options))
		foreach ( $options as $key => $val )
			$loc .= '&' . trim($key) . '=' . trim($val);
	elseif ($options) {
		$options = trim($options);
		if (strlen($options) > 0)
			$loc .= '&' . $options;
	}
	if (substr($loc, 0, 1) == '/') {
		$loc = substr($loc, 1);
	}
	return $loc;
}
function echo_app_url($action, $options = NULL, $is_control = false) {
	echo get_app_url($action, $options, $is_control);
}
function get_app_act() {
	$app = CTX()->app;
	$app_act = $app['grp'].'/'.$app['act'];
	if (isset($app['path'])) {
		$app_act = $app['path'].$app_act;
	}

	return $app_act;
}

function CTX() {
	return $GLOBALS['context'];
} // shortcut for $context

$context = RequestContext::instance();
$GLOBALS['context'] = $context;

function dev_log($message){
    
	if (defined('DEV_LOG')&& DEV_LOG){
            if(function_exists('app_dev_log')){
                app_dev_log($message);
            }
        }
}
  