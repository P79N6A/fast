<?php

require_lib('apiclient/Validation');
class ApiException extends Exception {
    private $scode;
    public function __construct($message, $scode = '1') {
        // 确保所有变量都被正确赋值
        parent::__construct($message, 0);
    }

    // 自定义字符串输出的样式
    public function __toString() {
        return __CLASS__ . ": [{$this->scode}]: {$this->message}\n";
    }
}

/**
 *
 * @example 接口路由示例：
 *          ?app_act=serverapi/router&m=common.test.get
 *          访问到model: common/TestModel::get方法
 * @author wzd
 *
 */

class serverapi {
    function router(array & $request, array & $response, array & $app) {
        try {
            $app['fmt'] = 'json';

            //日志
            $filepath = ROOT_PATH.'logs/server_api_'.date('Ymd').'.log';
             file_put_contents($filepath, "time:".date('Y-m-d H:i:s'), FILE_APPEND);
             file_put_contents($filepath, var_export($request,TRUE), FILE_APPEND);
             
            $params_str = $GLOBALS['HTTP_RAW_POST_DATA'];
            file_put_contents($filepath,'param:'.$params_str, FILE_APPEND);   
            
            
        //     echo 11;die;
             $validate = Validation::receive_form_server($request);

//
            if (FALSE == $validate['status']) {
                $validate['status'] = '-10004';
                return $response = $validate;
            } else {
                $data = $validate['data'];
            }
  

            $this->_hook('HOOK_BEFORE_SERVICE_EXECUTE', array(
                    $request,
                    $response,
                    $app
            ));

            $ret = $this->_call($data, $response, $app);

            $this->_hook('HOOK_AFTER_SERVICE_EXECUTE', array(
                    $ret
            ));
        } catch ( Exception $e ) {
            $ret = array(
                    'status' => $e->getCode(),
                    'message' => $e->getMessage()
            );
        }
        $this->_handle_response($ret, $request);
    }
    function _hook($name, $args) {
        static $cfg = FALSE;
        if ($cfg === FALSE) {
            $filename = APP_PATH . 'conf/api/hook.conf.php';

            if (file_exists($filename)) {
                $cfg = include $filename;
            } else {
                $filename = ROOT_PATH . 'conf/api/hook.conf.php';
                if (file_exists($filename)) {
                    $cfg = include $filename;
                }
            }
        }

        if (! isset($cfg[$name])) {
            return;
        }

        foreach ( $cfg[$name] as $hook ) {
            $ret = load_model($hook['class'])->handle();

            if ($ret['status'] != 1) {
                throw new ApiException($ret['message'], $ret['status']);
            }
        }
    }
    private function _call(array & $request, array & $response, array & $app) {
        if (! isset($request['method']) || empty($request['method'])) {
            throw new Exception('method must not empty!', - 510);
        }
        $method = $request['method'];

        $api_cfg = require_conf('serverapi');

        if (empty($api_cfg) || ! isset($api_cfg['alias']) || ! isset($api_cfg['api'])) {
            CTX()->log_error('serverapi.conf.php invalid! please check.');
            throw new Exception('method not support', - 512);
        }

        if (isset($api_cfg['alias'][$method])) {
            $method = $api_cfg['alias'][$method];
            list($c, $m) = explode('::', $method);
        } else {
            $act = $request['method'];
            $arr = explode('.', $act);
            $_index = count($arr);
            $m = $arr[$_index - 1];
            unset($arr[$_index - 1]);

            $arr[$_index - 2] = ucfirst($arr[$_index - 2]);
            $c = implode('/', $arr);
            $c = $c . 'Model';
        }
        // 如果方法没有配置，则认为不支持
        if (! isset($api_cfg['api'][$c]) || ! isset($api_cfg['api'][$c][$m])) {
            CTX()->log_error("model:{$c}, method:{$m} not in serverapi!");
            throw new Exception('method not support', - 511);
        }

        $model = null;

        try {
            $model = load_model($c);
            if (! method_exists($model, $m)) {
                throw new Exception('method ' . $m . ' not exist in model: ' . $c);
            }
        } catch ( Exception $e ) {
            CTX()->log_error($e->getMessage());
            throw new Exception('method unavailable', - 510);
        }
        $params_str = $GLOBALS['HTTP_RAW_POST_DATA'];
        $params = json_decode($params_str, true);
//        if (empty($api_cfg['api'][$c][$m])) {
//            $params = array($request);
//        } else {
//            $params = array();
//            $param_names = explode(',', $api_cfg['api'][$c][$m]);
//            foreach ($param_names as $name) {
//                if (!isset($request[$name])) {
//                	throw new Exception('lack-params:'.$name, -514);
//                }
//            	$params[] = $request[$name];
//            }
//        }

        return call_user_func_array(array($model, $m), $params);
    }
    private function _handle_response($ret, $request) {
        $type = 'json';
        if (isset($request['format']) && $request['format'] == 'xml') {
            $type = 'xml';
        }

        if ($type == 'json') {
            die(json_encode($ret));
        } else if ($type == 'xml') {
            require_lib('tb_xml');
            $xmlobj = new tb_xml();
            die($xmlobj->array2xml($ret, 'root'));
        }
        die('invalid type:' . $request['format']);
    }


}