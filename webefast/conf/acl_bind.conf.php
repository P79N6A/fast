<?php
/**
 * 权限项关联绑定配置
 * 
 * 背景：
 * 由于部分控制器方法不是菜单项或者按钮，但是这个控制器方法的权限应该是和某个操作是一样的。
 * 所以我们将某个控制器方法与操作的权限代码关联起来，这样在做权限检测时，会读取关联的操作权限代码来做控制
 * 
 * 配置数组说明：
 *      key：         操作的权限代码，如"sys/user/detail#scene=edit"
 *      value：     控制器方法标识 ，如"sys/user/do_edit"，如果有多个以逗号分隔（如"sys/user/do_edit,sys/user/do_edit_pwd"）
 * 
 * 为了减少配置，会将一些固定规则做自动关联：
 *      1、“path/class/do_{scene}”会自动和“path/class/detail#scene={scene}”，如“sys/user/do_edit”会自动和“sys/user/detail#scene=edit”关联
 */
$__acl_bind_cfg = array(
    // 操作权限代码                           =>  需要关联的控制器方法标识
// 	'sys/user/detail#scene=edit'=>'sys/user/do_edit',
//     'sys/user/detail#scene=add'=>'sys/user/do_add',
//     'sys/role/detail#scene=edit'=>'sys/role/do_edit',
//     'sys/role/detail#scene=add'=>'sys/role/do_add',
);

return $__acl_bind_cfg;