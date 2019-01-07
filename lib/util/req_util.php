<?php
/**
 * 删除本地和浏览器中name对应的cookie
 * @param string $name cookie的名称
 * @param boolean $pub 是否公共cookie
 */
function del_cookie($name,$pub=false){
	unset($_COOKIE[$name]);
	$GLOBALS['context']->set_cookie($name,'',-42000,$pub);
}
/**
 * 清除全部或部分session
 * @param string $name 清除 $name对应的session，如果为NULL，清除全部的session，默认为NULL.
 * @param $pub 是否公共session，仅对name不为空有效
 */
function clean_session($name=null, $pub=false){
		if(isset($GLOBALS['context']->app['mode']) && $GLOBALS['context']->app['mode']==='func'){
			$GLOBALS['context']->sess_init=false;
			return;
		}
		if($name) {
			$GLOBALS['context']->init_session();
			if(! $pub) $name='fAp'.$GLOBALS['context']->app_name . $name;
			unset($_SESSION[$name]);
		}else {	
			session_start();
			$_SESSION = array();		
			$params=session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,$params["path"], $params["domain"]);
			session_destroy();	
		}
}
/**
 * 得到session_id。
 */
function get_session_id () {
	$GLOBALS['context']->init_session ();
	return session_id();
}

/**
 * 得到 防止  跨站请求伪造(CSRF) 值，用于ajax生成dtsg_id_cffa，dtsg_ck_cffa的值。 
 * @param string $form_id post表单id
 * @param string $id  id
 * @param string $ck  校验码
 */
function get_dtsg($form_id,& $dtsg_id, &$dtsg_ck){
	$id=time();
	$dtsg_id= crc32($id . $form_id . $id);
	$dtsg_ck=hash_hmac('MD5',$dtsg_id,APP_SALT);	
}
/**
 * 输出防止  跨站请求伪造(CSRF) 隐藏域，必须在<form> </form>之间调用。 
 * @param string $form_id post表单id，默认'cffa'
 */
function put_dtsg_to_form($form_id='cffa'){
	get_dtsg($form_id,$id,$ck);
	echo "<input type='hidden' name='dtsg_id_{$form_id}' value='{$id}' />";
	echo "<input type='hidden' name='dtsg_ck_{$form_id}' value='{$ck}' />";	
}
/**
 * 校验 防止 跨站请求伪造(CSRF) 值
 * @param array $request	包含dtsg_id，dtsg_ck的输入请求数组。
 * @param string $form_id  post表单id，必须和put_anti_csrf，get_anti_csrf函数参数$form_id相同的值。
 * @return boolean true：合法，false：非法。
 */
function check_dtsg(& $request,$form_id='cffa'){
	$id="dtsg_id_{$form_id}";
	$ck="dtsg_ck_{$form_id}";
	if(! isset($request[$id]) || ! isset($request[$ck])) return false;
	$id=$request[$id];
	$ck=$request[$ck];
	return $id && $ck && $ck==hash_hmac('MD5',$id,APP_SALT) ;
}
/**
 * 返回浏览器的IP地址，依次查找HTTP_CLIENT_IP、HTTP_X_FORWARDED_FOR、REMOTE_ADDR的值，如果找不到IP，返回NULL
 */
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
       return NULL;
}
}

/**
 * 得到$action对应的URL
 * @param string $action 
 * @param boolean $is_control 是否是控件action
 */
function get_app_url_path($action,$is_control=false){
		$ctx=$GLOBALS['context'];
		if($is_control){
			$loc=$ctx->get_app_conf('base_url');
		}else{
			$loc=$ctx->get_app_conf('base_url');
		}
		return 	$loc;	
}
/**
 * 得到$action对应的query数组
 * @param string $action 
 * @param boolean $is_control 是否是控件action
 */
function get_app_url_query($action,$is_control=false){
		global $context;
		if($is_control){
			if(! $action)	$action=$context->app['ctl'];
			if(! $action) $action='do_index';
			$result=array('app_act'=>'ctl/index/do_index','app_ctl'=>$action);
		}else{
			if(! $action)	$action=$context->app_path.$context->app_clazz.'/'.'do_index';
			$result=array('app_act',$action);
		}	
		return $result;
}
/**
 * 输出URL
 * @param unknown_type $url_part
 * @param unknown_type $is_pub
 * @param unknown_type $is_theme
 * @param unknown_type $mutil_lang
 */
function echo_url($url_part,$is_pub=false,$is_theme=false,$mutil_lang=true){
	echo get_url($url_part,$is_pub,$is_theme,$mutil_lang);
}
/**
 * 输出app_url
 * @param $action
 * @param $options
 * @param $is_control
 */
function echo_app_url($action,$options=NULL,$is_control=false){
	echo get_app_url($action,$options,$is_control);
}
/**
 * 输出theme_url
 * @param string $url_part
 * @param string $mutil_lang
 */
function echo_theme_url($url_part,$mutil_lang=true){
	echo_url($url_part,false,true,$mutil_lang);
}
