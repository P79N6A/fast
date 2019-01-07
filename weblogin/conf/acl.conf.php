<?php
/*
 * 不需要检查登录状态的app_act列表
 */
$__acl_uncheck_login_list = array(
	'index/login',
    'index/cloud_login',
	'index/logout',
	'api/tb_itemcats/dl_tb_itemcats',
	'common/js/index',
    'index/login_enter',
    'index/login_by_platform',
    'index/ask_is_new_customer',
    'index/create_new_customer',
    'index/login_by_auth_key',
    'app/login',
);

/*
 * 自定义app_act对应的权限标识，权限标识默认与app_act同名，如rk/jhjrd/do_list的权限标识默认是rk/jhjrd/do_list
 */
$__acl_uncheck_priv_list = array(
    'index/cloud_login',
	'index/welcome',
    'index/do_index',
    'ctl/index/do_index',
	'api/tb_itemcats/dl_tb_itemcats',
	'common/js/index'
);

// 检查到未登录之后要跳转到的页面
$__acl_unlogin_redirect_page = 'index/login';

return array(
	'uncheck_login_list'=>$__acl_uncheck_login_list,
	'uncheck_priv_list'=>$__acl_uncheck_priv_list,
	'unlogin_redirect_page'=>$__acl_unlogin_redirect_page
	);