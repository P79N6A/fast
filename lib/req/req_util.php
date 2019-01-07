<?php
function del_cookie($name,$pub=false){
	unset($_COOKIE[$name]);
	$GLOBALS['context']->set_cookie($name,'',-42000,$pub);
}
function clean_session(){
		session_start();
		$_SESSION = array();
		$params=session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,$params["path"], $params["domain"]);
		session_destroy();
}
if(! function_exists('get_client_ip') ){
    function get_client_ip(){
       if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
          return getenv("HTTP_CLIENT_IP");
       else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
           return getenv("HTTP_X_FORWARDED_FOR");
       else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
           return getenv("REMOTE_ADDR");
       else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
           return $_SERVER['REMOTE_ADDR'];
       else
           return "unknown";
    }
}
function set_http_status($code) {
    static $_http_status = array(
        // Informational 100
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 200
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 300
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        //client Error 400
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        //server Error 500
        500 => 'Internal Server Error', 
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    if(array_key_exists($code,$_http_status)) {
        header("HTTP/1.1 {$code} {$_http_status[$code]}");
    }
}

function get_app_url_path($grp,$is_control=false){
		$ctx=$GLOBALS['context'];
		if($is_control){
			if($ctx->from_index)	$loc=$ctx->get_app_conf('base_url');
			else  $loc=$ctx->get_app_conf('base_url').'app/ctl/';
		}else{
			if($ctx->from_index)	$loc=$ctx->get_app_conf('base_url');
			else{
				if(! $grp)	$grp=$ctx->app['path'].$ctx->app['grp'];
				$loc=$ctx->get_app_conf('base_url')."app/{$grp}.". $ctx->get_app_conf('php_ext') ;
			}	
		}
		return 	$loc;	
}
function get_app_url_query($act,$grp,$is_control=false){
		$ctx=$GLOBALS['context'];
		if(! $act) $act='do_index';
		if($is_control){
			if(! $grp)	$grp=$ctx->app['ctl'];
			if($ctx->from_index) $result=array('app_grp'=>'ctl/index','app_ctl'=>$grp,'app_ctl_act'=>$act);
			else  $result=array('app_ctl'=>$grp,'app_ctl_act'=>$act);			
		}else{
			if(! $grp)	$grp=$ctx->app['path'].$ctx->app['grp'];
			if($ctx->from_index)	$result=array('app_act'=>$act,'app_grp'=>$grp);
			else  $result=array('app_act',$act);
		}	
		return $result;
}	