<?php
$u = array();
$u['52'] = array(
    "INSERT INTO `sys_action` VALUES ('7010110','7010001','url','待称重订单列表','oms/sell_record_cz/no_weighing_list','42','1','0','1','0');"
);
$u['bug_127'] = array(
    "DELETE FROM `sys_action` WHERE action_id=10000000;",
    "DELETE FROM `sys_action` WHERE action_name='订单转移';",
);

$u['99'] = array("INSERT INTO `order_check_strategy` (`check_strategy_code`, `is_active`, `instructions`, `content`, `lastchanged`) VALUES ('not_auto_confirm_with_store', '0', '配置仓库后，即此仓库对应的订单不会自动确认', '', '');");

$u['112'] = array(
    "alter table oms_sell_record_detail add key deal_code(deal_code);"
    );

$u['107'] = array(
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('return_auto_checked_and_return_money', '天猫退单智能处理', 'return_auto_checked_and_return_money', '', '0', '4', '天猫退单交易审核通过，将系统售后服务单自动确认;天猫退单交易退款成功，将系统售后服务单自动财务退款', '{\"app_act\":\"oms/sell_return/auto_checked_and_return_money\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '900', '0', 'sys', '', '0', NULL, '0');"
);

$U['bug_139'] = array(
    "ALTER TABLE `base_shop` MODIFY COLUMN `stock_source_store_code` VARCHAR(500);",
);