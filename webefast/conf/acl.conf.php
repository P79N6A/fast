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
    'index/auth_shop',
    'index/save_auth_shop',
    'index/login_by_auth_key',
    'index/login_by_form',
    'sys/sys_schedule/get_task_log',//自动服务日志 夸服务器获取时候需要
    'openapi/router',
     'wms/wmsapi/ydwms_api',
	      'wms/wmsapi/qimei_api',
         'wms/wmsapi/iwms_api',
         'index/login_app',
    'base/custom/register',
    'base/custom/do_register',
    'base/custom/register_search',
    'base/custom/do_register_search',
    'api/kdniao/api_kdniao/traces_push', //快递鸟接口推送服务
    'api/kdniao/api_kdniao/traces_subscribe', //快递鸟接口订阅服务
    'base/custom/captcha',//分销商注册验证码刷新
    'fx/account/pay_return',
    'base/store/get_area',//分销商注册省市联动
    'value/server_order/pay_return',
    'value/server_order/skip_new_url',
    'qianniu/login_by_qn',
    'qianniu/request_api',
    'work_bench/do_list',
    'oms/api_order/create_mijia_order',
    'wms/wmsapi/lifeng',
    'qmserver/router',
	'openapi/qimenapi',
    'erp/erpapi/qimen_api',
    'tool/osp/clear_conf',
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
$__acl_unlogin_redirect_page = CTX()->get_app_conf('login_server');

return array(
	'uncheck_login_list'=>$__acl_uncheck_login_list,
	'uncheck_priv_list'=>$__acl_uncheck_priv_list,
	'unlogin_redirect_page'=>$__acl_unlogin_redirect_page
	);