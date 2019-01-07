<?php
/*
 * 注册语句
 	if(defined('RUN_USER_DEBUG') && RUN_USER_DEBUG) $context->register_request_filter('UserDebugFilter','lib/filter/UserDebugFilter.class.php',
		create_function('$app','return !( defined("DEBUG") && DEBUG) && isset($app["user_debug"]);'));
 */
require_once ROOT_PATH.'boot/req_inc.php';

class UserDebugFilter implements IRequestFilter{
	function handle_before(array & $request,array & $response,array & $app){
		if(defined('DEBUG') && DEBUG) return;
		if( ! isset($app['user_debug']) || ! isset($app['user_id']) ) return;
		
		if( RequestContext::is_in_cli()){
			if( $app['user_debug'] !== APP_SALT) return;	//cli verify  sample, 'udy'-> user debug yes
		}else{
			if(RUN_SAFE===true && $app['mode']!='json' && ! isset($_COOKIE['app_user_debug']) ) return;
			
			if($app['user_debug'] !== self::digist($app['user_id']))  return;	//web verify method
		}
		$GLOBALS['app_debug']=true;
		$log=$GLOBALS['context']->log;
		$log->log_path .=$app['user_id'];
		$log->threshold = Log::DEBUG;
	}	
	static function digist($user_id){	//debug page set cookie to app_user_debug
		return  md5(APP_SALT.$user_id.self::APP_SALT);
	}
	
}
