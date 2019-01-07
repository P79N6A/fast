<?php

$u = array();
$u['572'] = array(
    "INSERT INTO sys_action (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7030000', '7000000', 'group', '发货数据查询', 'with-delivery-sdq', '2', '1', '0', '1', '0');",
    "INSERT INTO sys_action (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7030100', '7030000', 'url', '签收超时订单', 'oms/sell_record/overtime_list', '1', '1', '0', '1', '0');"
);
$u['575'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7010113', '7010103', 'act', '验货完成', 'oms/deliver_record/check#check_button', '1', '1', '0', '1', '0');"
);
$u['579'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020402', '4020400', 'act', '批量作废', 'oms/sell_record/opt_cancel', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020403', '4020400', 'act', '批量删除退款商品', 'oms/sell_record/btn_delete', '3', '1', '0', '1', '0');",
);

$u['571'] = array(
    //商品发布菜单
    "UPDATE `sys_action` SET `action_id`='91000000', `parent_id`='0', `type`='cote', `action_name`='商品发布', `action_code`='issue', `sort_order`='2', `appid`='1', `other_priv_type`='0', `status`='1', `ui_entrance`='3' WHERE (`action_id`='22020000');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91010000', '91000000', 'group', '淘宝商品发布', 'issue_tb', '1', '1', '0', '1', '3');",
    "UPDATE `sys_action` SET `action_id`='91010100', `parent_id`='91010000', `type`='url', `action_name`='宝贝上新', `action_code`='api/tb_issue/new_do_list', `sort_order`='1', `appid`='1', `other_priv_type`='0', `status`='1', `ui_entrance`='3' WHERE (`action_id`='22020100');",
    "UPDATE `sys_action` SET `action_id`='91020200', `parent_id`='91010000', `type`='url', `action_name`='宝贝列表', `action_code`='api/tb_issue/do_list', `sort_order`='2', `appid`='1', `other_priv_type`='0', `status`='1', `ui_entrance`='3' WHERE (`action_id`='22020200');",
    //商品上新权限
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5020205', '5020200', 'act', '商品上新', 'prm/goods/increment', '5', '1', '0', '1', '0');"
);
$u['bug_467'] = array(
    "ALTER TABLE `sys_carry_task`
        DROP INDEX `task_code` ,
        ADD UNIQUE INDEX `task_code` (`task_code`, `task_type`, `parent_task_code`) USING BTREE ;
        ",
);
$u['bug_480'] = array(
    "DELETE FROM sys_user_pref WHERE iid = 'goods_do_list/table';"
);
$u['582'] = array("INSERT INTO `base_sale_channel` (`sale_channel_code`,`short_code`,`sale_channel_name`,`is_system`,`is_active`) VALUES('mxyc','mxyc','明星衣橱','1','1');");

$u['583'] = array("INSERT INTO `base_sale_channel` (`sale_channel_code`,`short_code`,`sale_channel_name`,`is_system`,`is_active`) VALUES('xiaomi','xiaomi','小米','1','1');");

$u['bug_495'] = array(
    "ALTER TABLE base_custom CHANGE phone identification_number varchar(128) DEFAULT '' COMMENT '证件号码';"
);
