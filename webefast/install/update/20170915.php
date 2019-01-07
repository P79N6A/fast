<?php


$u['bug_1680'] = array(
    //修改配发货系统参数描述
    "UPDATE `sys_params` set `memo`='开启后，波次单中订单按照订单配送方式、商品库位进行排序生成序号，便于仓库打印快递单拣货' WHERE `param_code`='waves_create_sort_shelf';",
);

$u['1648'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('12010101', '12010100', 'act', '一键清理缓存', 'sys/erp_config/do_delete_cache', '2', '1', '0', '1', '1');"
);

$u['1650']=array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4030117', '4030100', 'act', '转退款单', 'oms/return_opt/opt_return_money', '8', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4030123', '4030100', 'act', '批量转退款单', 'oms/return_opt/opt_return_money_multi', '9', '1', '0', '1', '0');",
);
	
//openapi	
$u['1670'] = array(
	"INSERT INTO `sys_action` VALUES ('1100101','1100000','url','OPENAPI测试工具','api/tool','1','1','0','1','0');",
);

$u['1693'] = [
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'inv_cal_lock', 'op', '库存计算加锁定单库存', 'radio', '[\"关闭\",\"开启\"]', '1', '0.00', '1-启用 0-停用', NOW(), '开启后，库存计算时会添加锁定单设置的锁定库存');"
];
$u['1608'] = array(
    "ALTER TABLE `sys_role`
ADD UNIQUE INDEX `_role_code` (`role_code`) USING BTREE ;",
    "INSERT INTO `sys_role` 
(
role_code,
role_name,
role_type,
status,
sys,
role_desc,
remark)
VALUES ( 'security', '数据安全', 0, 1, 1, '该角色为系统内置，不可删除，拥有订单查询导出明文权限', '');",
);

$u['1704'] = [
    //淘宝中间表增加仓库字段
    "ALTER TABLE api_taobao_fx_order ADD `store_code` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '仓库代码';",
    "ALTER TABLE api_taobao_order ADD `store_code` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '仓库代码';"
];


