<?php
/*
 * 不需要检查权限的app_act列表
 */
$uncheck_act_list = array(
	'user/user/login',
	'user/user/logout',
);

// 检查到未登录之后要跳转到的页面
$__acl_unlogin_redirect_page = CTX()->get_app_conf('login_server');

return array(
	'uncheck_login_list'=>$__acl_uncheck_login_list,

	'unlogin_redirect_page'=>$__acl_unlogin_redirect_page
	);