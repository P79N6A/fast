<?php

$u['1961'] = array(
    "UPDATE `sys_role` SET `role_desc` = '该角色为系统内置，不可删除，拥有订单查询/售后服务单/会员列表导出明文权限' WHERE `role_code` = 'security';",
);
$u['1965'] = array(
    "alter table `pur_order_record` modify column `in_time` date COMMENT '入库期限'",
);
$u['bug_2017'] = array(
    //修改字段长度
    "ALTER TABLE oms_sell_record_detail MODIFY goods_code VARCHAR(64) DEFAULT '' NOT NULL COMMENT '商品代码';",
);
$u['bug_2069'] = array(

    "ALTER TABLE `api_dangdang_order`
DROP INDEX `orderID_itemID` ,
ADD UNIQUE INDEX `orderID_itemID` (`orderID`, `productItemId`) USING BTREE ;",
);
$u['1957'] = array(
	" INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) 
    VALUES ('cli_decrypt_api_order_time', '解挂订单包括未设置解挂时间的挂起订单', '', '', '0', '0', '默认开启。若该参数关闭，则系统自动解挂时不解挂未设置解挂时间的挂起订单', '{\"app_act\":\"cli/cli_decrypt_api_order\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '60', '0', 'sys', '', '0', NULL, '0')",
);
$u['task_1957'] = array(
	"DELETE FROM sys_schedule WHERE code='cli_decrypt_api_order_time' and name='解挂订单包括未设置解挂时间的挂起订单';",
);
$u['bug_1957'] = array("INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `memo`) VALUES ( 'cli_decrypt_api_order_time', 'oms_property', '解挂订单包括未设置解挂时间的挂起订单', 'radio', '[\"关闭\",\"开启\"]', '1', '0.00', '1-开启 0-关闭','默认开启。若该参数关闭，则系统自动解挂时不解挂未设置解挂时间的挂起订单');");
