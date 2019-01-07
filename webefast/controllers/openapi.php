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
 *          ?app_act=openapi/router&m=common.test.get
 *          访问到model: common/TestModel::get方法
 * @author wzd
 *
 */

class openapi {
    function qimenapi(array & $request, array & $response, array & $app) {
        try {
            $app['fmt'] = 'json';
            $filepath = ROOT_PATH.'logs/open_api/';
            if (!file_exists($filepath)){
                mkdir($filepath,0777, true);
            }
            $validate = Validation::receive_form_qimen($request);
            if (FALSE == $validate['status']) {
                $filepath = $filepath .'open_api_'.date('Ymd').'.log';//没有id就以年月日的形式记录
                file_put_contents($filepath, "time:".date('Y-m-d H:i:s'), FILE_APPEND);
                file_put_contents($filepath, var_export($request,TRUE), FILE_APPEND);
            } else {
                $data = $validate['data'];
                $filepath .= $data['kh_id'].DIRECTORY_SEPARATOR;
                if(!file_exists($filepath)){
                    mkdir($filepath,0777, true);
                }
                $filepath = $filepath .'open_api_'.date('Ymd').'.log';
                file_put_contents($filepath, "time:".date('Y-m-d H:i:s'), FILE_APPEND);
                file_put_contents($filepath, var_export($request,TRUE), FILE_APPEND);
            }
            $this->kh_id = $data['kh_id'];
            // 根据客户ID，切换数据库链接 ===========================================
            $this->change_db_conn();

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
    function router(array & $request, array & $response, array & $app) {
        try {
            $app['fmt'] = 'json';
            $filepath = ROOT_PATH.'logs/open_api/';
            if (!file_exists($filepath)){
                mkdir($filepath,0777, true);
            }
            $validate = Validation::receive_form_outside($request);
//            //测试用
//            if(RUN_MODE=='DEV'){
//                $validate['data']['kh_id'] = 12;
//            }

            if (FALSE == $validate['status']) {
                $filepath = $filepath .'open_api_'.date('Ymd').'.log';//没有id就以年月日的形式记录
                file_put_contents($filepath, "time:".date('Y-m-d H:i:s'), FILE_APPEND);
                file_put_contents($filepath, var_export($request,TRUE), FILE_APPEND);
                $validate['status'] = '-10004';
                return $response = $validate;
            } else {
                $data = $validate['data'];
                $filepath .= $data['kh_id'].DIRECTORY_SEPARATOR;
                if(!file_exists($filepath)){
                    mkdir($filepath,0777, true);
                }
                $filepath = $filepath .'open_api_'.date('Ymd').'.log';
                file_put_contents($filepath, "time:".date('Y-m-d H:i:s'), FILE_APPEND);
                file_put_contents($filepath, var_export($request,TRUE), FILE_APPEND);
            }
            $this->kh_id = $data['kh_id'];
            // 根据客户ID，切换数据库链接 ===========================================
            $this->change_db_conn();

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

        $api_cfg = require_conf('openapi');
        if (empty($api_cfg) || ! isset($api_cfg['alias']) || ! isset($api_cfg['api'])) {
            CTX()->log_error('openapi.conf.php invalid! please check.');
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
            CTX()->log_error("model:{$c}, method:{$m} not in openapi!");
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

        if (empty($api_cfg['api'][$c][$m])) {
            $params = array($request);
        } else {
            $params = array();
            $param_names = explode(',', $api_cfg['api'][$c][$m]);
            foreach ($param_names as $name) {
                if (!isset($request[$name])) {
                	throw new Exception('lack-params:'.$name, -514);
                }
            	$params[] = $request[$name];
            }
        }

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

    protected function change_db_conn() {

             //test
     //   return ;

         // 切换到运营中心据链接
//        $rds = load_model('api/ApiKehuModel')->get_rds_info_by_kehu($this->kh_id);
//
//        $init_info = array();
//        $init_info['client_id'] = $this->kh_id;
//        $init_info['rds_id'] = $rds['rds_id'];
//        $init_info['db_conf'] = array(
//                'name' => $rds['db_name'],
//                'host' => $rds['rds_link'],
//                'user' => $rds['rds_user'],
//                'pwd' =>  $rds['rds_pass'],
//                'type' => 'mysql',
//                'port' => '3306'
//        );
//        CTX()->saas->init_saas_client($init_info);
        
       load_model('api/ApiKehuModel')->change_db_conn($this->kh_id);


        return;
    }
    protected function get_rds_pass($kh_id) {
        // 切换到运营中心据链接
        CTX()->db->set_conf(array(
                'name' => CTX()->get_app_conf('db_name'), // 数据库名称
                'host' => CTX()->get_app_conf('db_host'), // 数据库链接地址
                'user' => CTX()->get_app_conf('db_user'), // 数据库用户名
                'pwd' => CTX()->get_app_conf('db_pass'), // 数据库密码
                'type' => 'mysql',
                'port' => '3306'
        ));
        $ret = load_model('ospbase/RdsModel')->get_rds_by_kh_id($kh_id);
        $keylock = get_keylock_string($ret['rds_createdate']);

        $result = create_aes_decrypt($ret['rds_pass'], $keylock);
        // $ret['data']['rds_pass'] = create_aes_decrypt('3E993FF72318FD76DCBDCCD8FCAD7E82',base64_encode(pack("H32",md5("54fa732c70bb2"))));
        return $result;
    }
}