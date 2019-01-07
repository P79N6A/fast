<?php
$u['1870'] = array(
    //平台交易列表
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010111', '4010100', 'act', '批量置为未转单', 'oms/sell_record/pl_td_untraned', '2', '1', '0', '1', '0');",
    //平台退单列表
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010404', '4010400', 'act', '转退单', 'oms/order_refund/set_refund', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010405', '4010400', 'act', '设为已处理', 'oms/order_refund/set_is_change', '2', '1', '0', '1', '0');",
);
$u['1814'] = array(
    "ALTER TABLE `op_gift_strategy_log` ADD `action_desc` varchar(300) Default '' COMMENT '操作描述' ",
);
$u['bug_1906'] = array(
    "UPDATE `sys_params` SET `memo` = '默认关闭，开启后，商品列表/商品库存查询/销售商品分析/销售数据分析显示扩展属性并支持导出' WHERE `param_code` = 'property_power';",
);

$u['bug_1909'] = array(
    "INSERT INTO sys_params (param_id, param_code, parent_code, param_name, type, form_desc, value, sort, remark, lastchanged, memo) VALUES ('', 'qimen_pur_return_type', 'pur', '奇门采购退货通知单类型配置', 'select', '[\"PTCK\",\"CGTH\"]', '0', 0.00, '', now(), '对接奇门，采购退货通知单上传时使用配置的类型');",
);