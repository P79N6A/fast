<?php
require_once ROOT_PATH . 'boot/req_inc.php';
class OperateLogFilter implements IRequestFilter {
    function handle_before(array & $request, array & $response, array & $app) {
        $this->access_log(array(
                'user_id' => CTX()->get_session('user_id'),
                'user_code' => CTX()->get_session('user_code'),
                'ip' => get_client_ip(),
                'op_time' => date('Y-m-d H:i:s'),
                'login_time' => CTX()->get_session('login_time', true),
                'url' => $_SERVER['REQUEST_URI'],
                'pre_url' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
        ));
    }
    
    private function access_log($data) {
    	error_log(implode("\t", $data)."\r\n", 3, APP_PATH.'logs/access_log.txt');
    }
}