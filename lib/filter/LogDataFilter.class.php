<?php
require_once ROOT_PATH.'boot/req_inc.php';
/*
 * 将下面代码插入到req_reg.php文件对应位置，可激活此filter
 * $context->register_request_filter('LogDataFilter','lib/filter/LogDataFilter.class.php',create_function('$app','return  $GLOBALS["app_debug"];')); //request数据写到日志模块
 * $context->register_response_filter('LogDataFilter','lib/filter/LogDataFilter.class.php',create_function('$app','return  $GLOBALS["app_debug"];'));//response数据写到日志模块
 */
class LogDataFilter implements IRequestFilter,IReponseFilter {
	function handle_before(array & $request,array & $response,array & $app){
		if(! $GLOBALS['app_debug']) return;
		
		$str = print_r($request, true);
		$GLOBALS['context']->log_debug("request data: {$str}");
		
		$str = print_r($app, true);
		$GLOBALS['context']->log_debug("app data:{$app}");
	}
	function handle_after(array & $request,array & $response,array & $app){
		if(! $GLOBALS['app_debug']) return;
		
		$str = print_r($response, true);
		$GLOBALS['context']->log_debug("response data:{$response}");
		
		$str = print_r($app, true);
		$GLOBALS['context']->log_debug("app data:{$app}");
	}
}





