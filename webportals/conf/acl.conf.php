<?php

/*
 * 不需要检查登录状态的app_act列表
 */
$__acl_uncheck_login_list = array(
    'login/init_login',
    'login/do_login',
    'login/do_logout',
    'tbauth/auth',
    'tbauth/do_auth',
    'login/captcha',
    'sys/session/save_session',
    'sys/session/get_sessionkey',
    'sys/session/callback_session',
    'apiv2/router',
    'api/kehu/get_kehu_info',
    'upgrade/upgrade/exec_upgrade_patch',
    'clients/shopinfo/set_databind',
    'index/do_xqfk_plug',
    'index/uploadxqfkimg',
    'index/do_subxqfk_plug',
    'moniter/mo_shop/create_moniter',
    'task/create',
    'api/ali_ecs/do_list',
    'sys_cli/tb_log',
    'sys/mailer/send',
    'api/kdniao/api_kdniao/traces_subscribe',
    'api/kdniao/api_kdniao/write_back_all',
    'sys/notice/auth_expired_notice',
);

/*
 * 自定义app_act对应的权限标识，权限标识默认与app_act同名，如rk/jhjrd/do_list的权限标识默认是rk/jhjrd/do_list
 */
$__acl_uncheck_priv_list = array(
    'index/do_welcome',
    'index/do_index',
    'ctl/index/do_index',
    'sys/user/do_chgpasswd#scene=edit',
    'common/select/org',
    'sys/org/getList',
    'common/select/user',
    'common/select/clientinfo',
    'common/select/orguser',
    'common/select/sellchannel',
    'basedata/sellchannel/getList',
    'common/file/upload',
    'servicenter/productissue/get_clients_info',
    'common/select/edition',
    'common/select/productmodule',
    'common/file/download_upload_file',
    'servicenter/productissue/do_research#scene=add',
    'servicenter/productissue/do_issueunable#scene=add',
    'sys/session/save_session',
    'sys/session/get_sessionkey',
    'index/do_xqfk_plug',
    'index/uploadxqfkimg',
    'index/do_subxqfk_plug',
    'api/ali_ecs/do_list',
);

// 检查到未登录之后要跳转到的页面
$__acl_unlogin_redirect_page = 'login/init_login';

return array(
    'uncheck_login_list' => $__acl_uncheck_login_list,
    'uncheck_priv_list' => $__acl_uncheck_priv_list,
    'unlogin_redirect_page' => $__acl_unlogin_redirect_page
);
