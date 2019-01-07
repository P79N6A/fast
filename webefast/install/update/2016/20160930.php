<?php

$u = array();

$u['659'] = array(
    "INSERT INTO `base_question_label` (`question_label_code`, `question_label_name`, `is_active`, `is_sys`, `content`, `remark`) VALUES ('CASH_ON_DELIVERY', '货到付款', '0', '1', NULL, '平台订单转单时，订单为货到付款，订单将自动设问');"
);

$u['658'] = array(
    "INSERT INTO `order_combine_strategy` (`rule_code`, `rule_status_value`, `rule_desc`, `rule_scene_value`, `remark`) VALUES ('order_combine_is_short', '0', '缺货单参与合并（合并后为缺货单）', '0', '');",
    "INSERT INTO `order_combine_strategy` (`rule_code`, `rule_status_value`, `rule_desc`, `rule_scene_value`, `remark`) VALUES ('order_combine_is_problem', '0', '问题单参与合并（合并后为问题单）', '0', '');"
);

$u['612'] = array("UPDATE `sys_schedule` SET `code`='echart_data', `name`='双十一看板数据更新', `task_type_code`='', `sale_channel_code`='', `status`='1', `type`='10', `desc`='开启后，双十一看板数据每隔5分钟刷新一次', `request`='{\"app_act\":\"sys/echarts/saveChartsDataByTask\",\"app_fmt\":\"json\"}', `path`='webefast/web/index.php', `max_num`='0', `add_time`='0', `last_time`='0', `loop_time`='300', `task_type`='0', `task_module`='sys', `exec_ip`='', `plan_exec_time`='0', `plan_exec_data`='', `update_time`='0' WHERE (`code`='echart_data');
");

$u['662'] = array(
    " UPDATE `sys_action` SET `action_name` = '直接发货' WHERE `action_id` = '7010113'"
);

$u['669'] = array(
    "ALTER TABLE base_shop ADD COLUMN `inv_syn` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品无库存记录以0库存同步 不启用=0，启用=1'"
);

$u['660'] = array(
    //唯品会JIT多PO菜单
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040600', '8040000', 'url', '多PO拣货单管理', 'api/api_weipinhuijit_multi_po_pick/do_list', '2', '1', '0', '1', '0');",
    //多PO字段修改
    "ALTER TABLE `api_weipinhuijit_pick` MODIFY COLUMN `po_no` varchar(500) NOT NULL COMMENT 'PO单编号';",
    "ALTER TABLE `api_weipinhuijit_delivery` MODIFY COLUMN `po_no` varchar(500) NOT NULL COMMENT 'PO单编号';",
    "ALTER TABLE `api_weipinhuijit_store_out_record` MODIFY COLUMN `po_no` varchar(500) NOT NULL COMMENT 'PO单编号';",
    "ALTER TABLE `api_weipinhuijit_pick` ADD COLUMN `jit_version` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'JIT接口版：1-1.0;2-2.0';",
    "ALTER TABLE `api_weipinhuijit_delivery_detail` ADD `po_no` varchar(50) NOT NULL COMMENT 'PO单编号' AFTER `pid`;"
);

$u['687'] = array(
    "insert into base_area set id = '360125000000',type = 4,name = '昌北区',parent_id = '360100000000',lastchanged = now();",
    "insert into base_area set id = '320510000000',type = 4,name = '平江区',parent_id = '320500000000',lastchanged = now();"
);
