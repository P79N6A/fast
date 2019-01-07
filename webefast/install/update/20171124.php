<?php
$u['1830'] = array(
    //外包仓进销存单
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7030202', '7030102', 'act', '批量处理', 'wms/wms_mgr/opt_order_shipping_b2b', '2', '1', '0', '1', '0');",
);

$u['1813'] = array(
    //唯品会JIT 出库单管理，批量出库加入权限控管
    "INSERT INTO `sys_action` (`action_id`,`parent_id`,`type`,`action_name`,`action_code`,`sort_order`,`appid`,`other_priv_type`,`status`,`ui_entrance`) VALUES('8040320','8040300','act','批量出库','api/api_weipinhuijit_delivery/batch_confirm_delivery','2','1','0','1','0');",
);

$u['1799'] = array(
    " ALTER TABLE oms_waves_record ADD COLUMN `is_print_waves` tinyint(3) DEFAULT '0' COMMENT '是否打印波次单' AFTER is_print_goods;"
);

$u['1833'] = array(
    //分销订单
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070314', '8070300', 'act', '等价换货', 'oms/order_opt/fx_change_goods', '3', '1', '0', '1', '0');",
    //网络订单
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020205', '4020300', 'act', '等价换货', 'oms/order_opt/oms_change_goods', '3', '1', '0', '1', '0');",
);


$u['1834'] = array(
    'insert into `sys_params` ( `param_code`, `parent_code`, `memo`, `type`, `value`, `lastchanged`, `form_desc`, `remark`, `param_name`, `sort`, `param_id`) values ( \'opt_confirm_get_cainiao\', \'oms_property\', \'仅针对云栈(菜鸟)四期，默认关闭。开启后，订单点击确认按钮后，系统自动获取菜鸟运单号。\', \'radio\', \'0\', \'2017-11-16 11:22:40\', \'[\"关闭\",\"开启\"]\', \'1-开启 0-关闭\', \'订单确认时自动获取菜鸟运单号\', \'12.00\', \'\');'
);

$u['1805'] = array(
    //订单列表
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020344', '4020300', 'act', '批量确认', 'oms/order_opt/pl_opt_confirm', '3', '1', '0', '1', '0');",
);

$u['1814'] = array(
    //平台退单列表
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010401', '4010400', 'act', '批量转退单', 'oms/order_refund/opt_set_refund', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010402', '4010400', 'act', '批量设为已处理', 'oms/order_refund/opt_set_is_change', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010403', '4010400', 'act', '批量设为未处理', 'oms/order_refund/opt_set_no_change', '3', '1', '0', '1', '0');",
    "ALTER TABLE `api_refund` ADD `is_hand_change` tinyint(1)  NOT NULL Default 0 COMMENT '手动设为已处理 0-不是 1-是' ",
);