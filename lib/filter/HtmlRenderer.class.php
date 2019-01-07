<?php
require_once ROOT_PATH.'boot/req_inc.php';

//web页面模板处理
if(!defined('WEB_PAGE_TPL')) define('WEB_PAGE_TPL','web_page.tpl.php'); //web页面模板
if(!defined('WEB_PAGE_ERR_TPL')) define('WEB_PAGE_ERR_TPL','web_page_error.tpl.php'); //错误页面模板
if(! defined('RUN_WIDGET') || ! RUN_WIDGET){
	function render_widget($widget_id){}
}
if(! defined('RUN_CONTROL') || ! RUN_CONTROL){
	function render_control($clazz,$id,array $options=array()){}
}
function get_web_page($tpl_file) {
	$dirpath = dirname ( $tpl_file );
	if (! $dirpath)	return WEB_PAGE_TPL;
	if (DIRECTORY_SEPARATOR != '/')	$dirpath = str_replace ( '/', DIRECTORY_SEPARATOR, $dirpath );
	if ($dirpath == '/' || $dirpath == '/')	return $dirpath . WEB_PAGE_TPL;
	if (file_exists ( $dirpath . DIRECTORY_SEPARATOR . WEB_PAGE_TPL )) return $dirpath . DIRECTORY_SEPARATOR . WEB_PAGE_TPL;

	$ctx = $GLOBALS ['context'];
	$views_path = ROOT_PATH . $ctx->app_name . DIRECTORY_SEPARATOR . 'views';
	if ($ctx->theme) $views_path .= DIRECTORY_SEPARATOR . $ctx->theme;
	if( defined('RUN_MLANG_VIEW') && RUN_MLANG_VIEW) $views_path.=$ctx->app_lang .DIRECTORY_SEPARATOR ;

	while ( $dirpath && $dirpath != DIRECTORY_SEPARATOR && $dirpath != $views_path ) {
		$pos = strrpos ( $dirpath, DIRECTORY_SEPARATOR );
		$dirpath = substr ( $dirpath, 0, $pos );
		if (file_exists ( $dirpath . DIRECTORY_SEPARATOR . WEB_PAGE_TPL )) return $dirpath . DIRECTORY_SEPARATOR . WEB_PAGE_TPL;
	}
}
if(! function_exists('get_tpl_path')){
	function get_tpl_path($tplname) {
		global $context;
		$views_path=ROOT_PATH . $context->app_name . DIRECTORY_SEPARATOR.'views'. DIRECTORY_SEPARATOR ;
		if($context->theme) $views_path.=$context->theme .DIRECTORY_SEPARATOR ;
		if( defined('RUN_MLANG_VIEW') && RUN_MLANG_VIEW) $views_path.=$context->app_lang .DIRECTORY_SEPARATOR ;
		if('/'!==DIRECTORY_SEPARATOR) $tplname=str_replace('/',DIRECTORY_SEPARATOR,$tplname);
		return $views_path.$tplname.'.tpl.php';
	}
}
/**
 * 放置分页条
 * @param array  $page_info 分页信息   TableMapper->get_page_info函数返回值,@see TableMapper
 * @param string $page_link  页码链接模板
 * @param string $page_tpl   分页条模板文件，在views模板目录的pagination子目录下，默认值page，即page.tpl.php文件。
 */
function put_pagination(array $page_info, $page_link, $page_tpl = 'page') {
	$_fa_file_X3f7t_o0z8 = get_tpl_path ( 'pagination' . DIRECTORY_SEPARATOR . $page_tpl );
	if (! file_exists ( $_fa_file_X3f7t_o0z8 )) {
		global $context;
		$context->log_error ( "pagination [{$_fa_file_X3f7t_o0z8}] NOT FOUND" );
	} else {
		unset ( $page_tpl );
		include $_fa_file_X3f7t_o0z8;
	}
}

//end web页面模板处理

class HtmlRenderer implements IReponseRenderer {
	private function render_error(array & $app,$view_path,$none_page){
		if($app['err_no']==0) return;
		if(! file_exists( $view_path. WEB_PAGE_ERR_TPL)){
			$GLOBALS['context']->log_error ( 'ERROR PAGE TEMPLATE '.$view_path. WEB_PAGE_ERR_TPL .' NOT FOUND!'  );
			echo '<html><head>';
			if(isset($app['title'])) 	echo '<title>'.$app['title'].'</title></head><body>';
 			echo '<h1>ERROR PAGE TEMPLATE NOT FOUND!</h1></body></html>';
 			return;
		}
		if($none_page) include $view_path. WEB_PAGE_ERR_TPL;
		else{	//add head and tail for app_page=null,WEB_PAGE_ERR_TPL is not full page
			echo "<html><head>\n<meta http-equiv='content-Type' content='text/html; charset=".
				$GLOBALS['context']->get_app_conf('charset') ."'/>\n";
			if(isset($app['title'])) 	echo '<title>'.$app['title']."</title></head><body>";
 			include $view_path. WEB_PAGE_ERR_TPL;
			echo "\n</body></html>";
		}
		return;
	}

	private function _get_tpl($app_path, $app, $_views_path) {
	    if(isset($app['tpl']) && $app['tpl']){
	        $main_child_tpl=$app['tpl'];
	        if($main_child_tpl[1]!==':' && $main_child_tpl[0]!=='/') 	//not absolute page path
	            $main_child_tpl= $_views_path."{$main_child_tpl}.tpl.php";
	    }
	    else{
	        $main_child_tpl= $_views_path."{$app_path}";
	        if(defined('RUN_TPL_DIR') && RUN_TPL_DIR){
	            $main_child_tpl.= $app['grp'] . DIRECTORY_SEPARATOR;

	            if(defined('RUN_TPL_ACT_FILE') && RUN_TPL_ACT_FILE)
	                $main_child_tpl .= $app['act'].'.tpl.php';
	            else $main_child_tpl .= "{$app['grp']}_{$app['act']}.tpl.php";

	        }
	        else $main_child_tpl .= "{$app['grp']}_{$app['act']}.tpl.php";
	    }

	    return $main_child_tpl;
	}
	function render(array & $request,array & $response,  array & $app) {
		if ($app ['fmt'] !== 'html') return;

		$context=$GLOBALS['context'];
		$webpage_charset=$context->get_app_conf('charset');
		header('Content-Type: text/html;charset='.$webpage_charset);
		if(isset($app ['ttl']) && $app ['ttl']>0)	header("Cache-Control:max-age={$app ['ttl']}");

		$_none_page=isset($app['page'])&& strcasecmp($app['page'],'NULL')==0;

		$_views_path=ROOT_PATH . $context->app_name . DIRECTORY_SEPARATOR.'views'. DIRECTORY_SEPARATOR ;
		if($context->theme) $_views_path .= $context->theme . DIRECTORY_SEPARATOR ;
		if( defined('RUN_MLANG_VIEW') && RUN_MLANG_VIEW) $_views_path .= $context->app_lang .DIRECTORY_SEPARATOR ;

		if($app['err_no']!==0){
			$this->render_error($app,$_views_path,$_none_page);
			return;
		}
		$main_child_tpl = $this->_get_tpl($context->app_path, $app, $_views_path);
		if(! file_exists($main_child_tpl)){
		    $_views_path=ROOT_PATH . FT_COMMON_DIR . DIRECTORY_SEPARATOR.'views'. DIRECTORY_SEPARATOR ;
		    if($context->theme) $_views_path .= $context->theme . DIRECTORY_SEPARATOR ;
		    if( defined('RUN_MLANG_VIEW') && RUN_MLANG_VIEW) $_views_path .= $context->app_lang .DIRECTORY_SEPARATOR ;
		    $main_child_tpl = $this->_get_tpl($context->app_path, $app, $_views_path);
		}
		if(! file_exists($main_child_tpl)){
			$app['err_no']=20001;
			$app['err_msg']=lang('req_err_not_found_tpl').'['. $main_child_tpl.']';
			$this->render_error($app,$_views_path,$_none_page);
			return;
		}

		if ($_none_page) {
			$_FASTAPP_web_page_file_3D8jKw5L2q=$main_child_tpl;
			unset($main_child_tpl);
			include $_FASTAPP_web_page_file_3D8jKw5L2q;
			return true;
		} else {
			if (! isset ( $app ['page'] ))	$_web_page_file = get_web_page ( $main_child_tpl );
			else{
				$_web_page_file= $app ['page'];
				/*  由于安全原因，取消app_page使用绝对路径特性，即app_page必须在对应的views目录theme子目录下
				//if($_web_page_file[1]!==':' && $_web_page_file[0]!=='/') 	//not absolute page path
				 */
					$_web_page_file = $_views_path . $_web_page_file . '.tpl.php';

			}
			$_FASTAPP_web_page_file_3D8jKw5L2q=$_web_page_file;
			unset ( $_views_path );
			unset ( $_none_page );
			unset ( $_web_page_file );

			if (file_exists ( $_FASTAPP_web_page_file_3D8jKw5L2q )) include $_FASTAPP_web_page_file_3D8jKw5L2q;			//the file MUST NOT contain var $main_child_tpl
			else {
				$_FASTAPP_web_page_file_3D8jKw5L2q=$main_child_tpl;
				unset($main_child_tpl);
                $charset = $GLOBALS['context']->get_app_conf('charset');
                $charset = empty($charset) ? 'utf-8' : $charset;
				echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\"><head>\n<meta http-equiv='content-Type' content='text/html;' charset='".$charset."' />\n";
				if (isset ( $app ['title'] ))	echo '<title>' . $app ['title'] . "</title></head><body>\n";
				include $_FASTAPP_web_page_file_3D8jKw5L2q;
				echo "\n</body></html>";
			}

			return true;
		}
	}
}

