<?php
require_lib('util/web_util', true);


class Api {
	function route(array & $request,array & $response,array & $app) {
		$_log_id = time();
		error_log('['.date('Y-m-d H:i:s')."] {$_log_id}***:".var_export($request, true)."\r\n", 3, ROOT_PATH.'logs/oa_request_log.txt');
		$ret = load_model('api/Route_model')->route($request);
		$resp = json_encode($ret);
		error_log('['.date('Y-m-d H:i:s')."] {$_log_id}***:".var_export($request, true)."\r\n", 3, ROOT_PATH.'logs/oa_request_log.txt');
		echo $resp;
		exit;
	}
	
	function test() {
		$ret = load_model('api/Route_model')->route($request);
		echo json_encode($ret);
		exit;
	}
	
	function tool() {
	}
}