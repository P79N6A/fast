<?php

$u['2073']=array(
    "ALTER TABLE `stm_store_shift_record` ADD COLUMN `is_print_record` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否打印移仓单 0-未打印，1-已打印'  ",
);

$u['2074']=array(
    "ALTER TABLE `pur_planned_record` ADD COLUMN `is_print_record` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否打印计划单 0-未打印，1-已打印'  ",
    "ALTER TABLE `pur_purchaser_record` ADD COLUMN `is_print_record` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否打印入库单 0-未打印，1-已打印'  ",
    "ALTER TABLE `pur_return_record` ADD COLUMN `is_print_record` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否打印退货单 0-未打印，1-已打印'  ",
);

$u['2075']=array(
    "ALTER TABLE `wbm_notice_record` ADD COLUMN `is_print_record` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否打印通知单 0-未打印，1-已打印'  ",
    "ALTER TABLE `wbm_store_out_record` ADD COLUMN `is_print_record` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否打印销货单 0-未打印，1-已打印'  ",
    "ALTER TABLE `wbm_store_out_record` ADD COLUMN `is_print_box` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否打印汇总单 0-未打印，1-已打印'  ",
    "ALTER TABLE `wbm_return_record` ADD COLUMN `is_print_record` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否打印退货单 0-未打印，1-已打印'  "
);

$u['2060'] = array(//快捷菜单
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`)	VALUES ('1011100', '1010000', 'url', '快捷菜单设置', 'sys/shortcut_menu/do_list', '10', '1', '0', '1', '0');",
    "CREATE TABLE `sys_shortcut_menu` (
        `shortcut_menu_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT '0' COMMENT '用户id',
        `action_id` int(11) DEFAULT '0' COMMENT '菜单id',
        PRIMARY KEY (`shortcut_menu_id`),
        UNIQUE KEY `_index_key` (`user_id`,`action_id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户快捷菜单表';",
);

$u['2070'] = array(
    "INSERT INTO `sys_action` (`action_id`,`parent_id`,`type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020345','4020300', 'url', '补单', 'oms/order_opt/opt_replenish', '4', '1', '0', '1', '0');",
    "ALTER TABLE `oms_sell_record` ADD COLUMN `is_replenish`  tinyint(3) NULL DEFAULT 0 COMMENT '是否是补单' AFTER `is_receive`;",
    "ALTER TABLE `oms_sell_record` ADD COLUMN `is_replenish_from`  varchar(20) NOT NULL DEFAULT '' AFTER `is_replenish`;",
);

$u['2068'] = array(
    "INSERT INTO order_check_strategy (check_strategy_code, is_active, instructions, content, lastchanged) VALUES ('not_auto_confirm_with_money', 0, '配置金额后，即订单金额超出配置的金额范围之外的订单不能自动确认', '', now());"
);
//给订单表添加发票抬头类型字段
$u['2058'] = array(
   "ALTER TABLE oms_sell_record ADD COLUMN invoice_title_type tinyint(3) NOT NULL DEFAULT '0' COMMENT '发票抬头类型 0个人 1企业';",
);

$u['1314'] = array("INSERT INTO `base_sale_channel` (`sale_channel_code`,`short_code`,`sale_channel_name`,`is_system`,`is_active`) VALUES('sappho','sp','莎孚','1','1');");

$u['2050'] = array(
    "INSERT INTO sys_action (action_id, parent_id, type, action_name, action_code, sort_order, appid, other_priv_type, status, ui_entrance) VALUES (6030307,6030300, 'act', '二维表导入采购订单', 'pur/planned_record/layer_import', 0, 1, 0, 1, 0);"
);

$u['2069'] = array("INSERT INTO `base_record_type` (`record_type_code`, `record_type_name`, `record_type_property`, `sys`, `remark`, `lastchanged`) VALUES ('order_replenish', '订单漏发', '8', '1', '系统内置档案，不允许删除', '2016-01-23 14:59:29');");

$u['bug_2152'] = array(
    "ALTER TABLE oms_sell_record_notice MODIFY COLUMN `goods_weigh` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '商品总重量-千克';"
);
$u['2064']=array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5020800', '5020000', 'url', '组装商品列表', 'prm/goods/do_list_diy', '1', '1', '0', '1', '0');",
    "INSERT INTO sys_role_action(`role_id`,`action_id`) SELECT `role_id`,'5020800' AS `action_id` FROM sys_role_action WHERE action_id = '5020000'"
);
$u['bug_2242']=array(
    "update sys_params set memo='默认关闭，开启后，商品列表/组装商品列表/商品库存查询/销售商品分析/销售数据分析/批发统计分析的查看商品明细/采购统计分析的查看商品明细，显示扩展属性并支持导出；且批发销货通知单/批发退货通知单/采购通知单/采购退货通知单，导出明细时导出扩展属性' where param_code='property_power';",
);
