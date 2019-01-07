<?php

$u = array();

$u['922'] = array(
    "ALTER TABLE base_supplier ADD `province` varchar(20) DEFAULT NULL COMMENT '省' AFTER `tel`;",
    "ALTER TABLE base_supplier ADD `city` varchar(20) DEFAULT NULL COMMENT '市' AFTER `province`;",
    "ALTER TABLE base_supplier ADD `district` varchar(20) DEFAULT NULL COMMENT '区' AFTER `city`;",
    "ALTER TABLE base_supplier ADD `street` varchar(20) DEFAULT NULL COMMENT '街道' AFTER `district`;"
);

$u['920'] = array(
    "ALTER TABLE stm_goods_diy_record ADD COLUMN `record_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '单据类型 0组装 1拆分'",
    "UPDATE stm_goods_diy_record SET record_type=0 WHERE num>0",
    "UPDATE stm_goods_diy_record SET record_type=1 WHERE num<0"
);

$u['917'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9060600', '9060000', 'url', '收款统计', 'fx/collection_statistic/do_list', '12', '1', '0', '1', '0')"
);
$u['938'] = array(
//门店库存流水账菜单
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('30010400', '30010000', 'url', '库存流水账', 'prm/inv_record/entity_do_list', '12', '1', '0', '1', '0');",
    "DELETE FROM sys_role_action WHERE role_id IN (SELECT role_id FROM sys_role WHERE role_code = 'oms_shop') AND action_id IN ('6000000','6020000','6020100', '6020200');",
    "INSERT INTO sys_role_action (role_id, action_id) SELECT sr.role_id,'30010400' action_id FROM sys_role sr WHERE sr.role_code = 'oms_shop';"
);

$u['bug_775'] = array(
    //装箱单菜单id修改，增加删除权限
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8050201', '8050200', 'act', '修改', 'b2b/box_record/edit', '1', '1', '0', '1', '0');",
    "UPDATE sys_action SET action_id='8050100' WHERE action_id='8050001';",
    "UPDATE sys_action SET action_id='8050200' WHERE action_id='8050002';"
);

$u['937'] = array(
	"insert into sys_action values('22010300','22010000','url','我要贷款','sys/service/translation',3,1,0,1,0);",
);