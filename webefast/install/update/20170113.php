<?php

$u = array();

$u['984'] = array(
    "ALTER TABLE oms_sell_record ADD COLUMN `invoice_number` varchar(100) NOT NULL DEFAULT '' COMMENT '发票号' AFTER `invoice_status`;",
    "ALTER TABLE sap_sell_record ADD COLUMN `invoice_number` varchar(100) NOT NULL DEFAULT '' COMMENT '发票号';"
);

$u['979'] = array(
    //网络订单，订单流程菜单移动最下面
    "UPDATE sys_action SET sort_order=6 WHERE action_id='4050000' ",
    //网络分销，分销商列表移动到基础数据-分销商档案
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('2070000', '2000000', 'group', '分销商档案', 'base_custome_record', '4', '1', '0', '1', '0');",
    "UPDATE sys_action SET parent_id='2070000' WHERE action_id='8010100' ",
    //网络分销，唯品会JIT菜单移动到进销存模块（批发管理下）
    "UPDATE sys_action SET parent_id='6000000',sort_order=5 WHERE action_id='8040000'",
    //进销存，装箱单合并打到批发模块，放到批发销货单下面
    "UPDATE sys_action SET parent_id='8020000',sort_order=4 WHERE action_id='8050200'",
    " UPDATE sys_action SET sort_order=5 WHERE action_id='8020400'",
    "UPDATE sys_action SET sort_order=6 WHERE action_id='8020200'",
    "UPDATE sys_action SET sort_order=7 WHERE action_id='8020500'",
    //1）网络批发模块-》批发管理
    "UPDATE sys_action SET action_name='批发管理' WHERE action_id='8020000'",
    //去掉装箱任务列表
    "DELETE FROM sys_action WHERE action_id='8050100'",
    "DELETE FROM sys_action WHERE action_id='8050000'",
    //商品组装单，移动到库存维护之上
    "UPDATE sys_action SET sort_order=6 WHERE action_id='6010800' ",
    "UPDATE sys_action SET sort_order=7 WHERE action_id='6010700' ",
);
$u['993'] = array(
    "insert into sys_action values('2020201','2020200','act','删除','base/shelf/do_delete_store',1,1,0,1,0);",
);

$u['985'] = array(
    "CREATE TABLE `iwms_bill_data` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_code` VARCHAR (50) NOT NULL DEFAULT '' COMMENT 'wms单据号',
	`record_type` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '单据类型：shift-移仓单',
	`record_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '业务时间',
	`remark` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '备注',
	`record_data` text NOT NULL DEFAULT '' COMMENT '明细数据（json）',
	`is_deal` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '处理状态：0-未处理;1-已处理;2-处理失败',
	`fail_num` SMALLINT (5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '处理失败次数',
	`fail_reason` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '处理失败原因',
	`create_time` INT (11) NOT NULL DEFAULT '0' COMMENT '创建时间',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_code` (`record_code`,`record_type`) USING BTREE,
	KEY `ind_bill_code` (`record_code`) USING BTREE,
	KEY `ind_record_type` (`record_type`) USING BTREE,
	KEY `ind_is_deal` (`is_deal`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = 'IWMS单据数据';",
    //移仓单主表增加唯一键
    "ALTER TABLE `stm_store_shift_record` ADD UNIQUE KEY `uni_code` (`record_code`) USING BTREE;",
    //IWMS移仓单处理自动服务
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('iwms_shift_bill_deal', 'IWMS移仓单处理', 'iwms_shift_bill_deal', '', '0', '2', '', '{\"app_act\":\"wms\\/wms_mgr\\/iwms_shift_bill_deal\"}', 'webefast/web/index.php', '0', '0', '0', '1800', '0', 'sys', '', '0', NULL, '0');"
);

$u['902'] = array(
    "UPDATE sys_action SET sort_order=72 WHERE action_id='91000000';"
);

$u['1002'] = array("INSERT INTO `base_sale_channel` (
                    `sale_channel_code`,
                    `short_code`,
                    `sale_channel_name`,
                    `is_system`,
                    `is_active`)
                VALUES (
                    'ofashion',
                    'ofashion',
                    'ofashion',
                    '1',
                    '1');");
$u['973'] = array(
		"delete from sys_action where action_id = '2040100' or parent_id = '2040100';",
		"delete from sys_action where action_id = '2040300' or parent_id = '2040300';",
		"update sys_action set action_name='业务类型',action_code='base/record_type/do_list' where action_id='2040200';",

		"update sys_action set action_code='base/record_type/detail#scene=add' where action_id='2040201';",

		"update sys_action set action_code='base/record_type/detail#scene=edit' where action_id='2040202';",

		"update sys_action set action_code='base/record_type/delete#scene=edit' where action_id='2040203';"
);
$u['980'] = array(
	"insert into sys_action values('4020801','4020800','act','修改配发货,快递单号','oms/deliver_record/edit_express',1,1,0,1,0);",
	"insert into sys_action values('7010121','7010111','act','修改配发货,快递单号','oms/deliver_record/edit_express_new',1,1,0,1,0);",
);