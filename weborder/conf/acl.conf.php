<?php
/*
 * 不需要检查权限的app_act列表
 */
$uncheck_act_list = array(
	'user/user/login',
	'user/user/logout',
  'product/soonbuy/pay_return',
	'product/soonbuy/skip_new_url'
);

/*
 * 自定义app_act对应的权限标识，权限标识默认与app_act同名，如rk/jhjrd/do_list的权限标识默认是rk/jhjrd/do_list
 */
$act_map_list = array(
	
);