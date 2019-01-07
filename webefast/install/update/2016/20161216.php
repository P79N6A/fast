<?php

$u = array();

$u['906'] = array(
    "UPDATE `sys_action` SET `action_id`='8030000', `parent_id`='4000000' WHERE (`action_id`='8030000');",
    "UPDATE `sys_action` SET `action_name`='分销商分类' WHERE (`action_id`='8010400');",
    "DELETE FROM sys_action WHERE `action_id`='8030000';",
    "UPDATE `sys_action` SET `action_id`='4010600', `parent_id`='4010000', `sort_order`='6' WHERE (`action_id`='8030100');",
    "UPDATE `sys_action` SET `action_id`='4010700', `parent_id`='4010000', `sort_order`='7' WHERE (`action_id`='8030200');",
    "UPDATE `sys_action` SET `action_id`='4010800', `parent_id`='4010000', `sort_order`='8' WHERE (`action_id`='8030300');",
);

$u['853'] = array(
    "ALTER TABLE fx_income_pay MODIFY `detail_type` tinyint(1) DEFAULT NULL COMMENT '明细类型:0-资金账户生成;1-业务单据生成';",
    "ALTER TABLE fx_income_pay ADD COLUMN `img_url` varchar(255) DEFAULT '' COMMENT '主图地址';",
    "ALTER TABLE fx_income_pay ADD COLUMN `thumb_img_url` varchar(255) DEFAULT '' COMMENT '缩略图地址';"
);

$u['897'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('14020300', '14020000', 'url', '天猫积分抵扣', 'sys/tmall_integration/do_list', '1', '1', '0', '1', '1');"
);

$u['bug_780'] = array(
    "delete from sys_user_pref where iid='oms/sell_record_td_list';",
);

$u['bug_788'] = array(
    "ALTER TABLE `api_weipinhuijit_wms_info`
MODIFY COLUMN `arrival_time`  datetime NULL DEFAULT NULL COMMENT '要求到货时间' AFTER `delivery_method`;",
);

$u['827_1'] = array(
	"ALTER TABLE crm_goods ADD `lock_num` int(10) NOT NULL DEFAULT '0' COMMENT '活动锁定库存';",
);
$u['bug_803'] = array(
    "UPDATE base_express_company set rule='^[GA]{2}[0-9]{9}([2-5][0-9]|[1][1-9]|[6][0-5])$|^[99]{2}[0-9]{11}$|^[96]{2}[0-9]{11}$|^[98]{2}[0-9]{11}$' WHERE company_code='POSTB';",
);
$u['918'] = array(
	"insert into sys_action values('3020810','3020800','act','删除','op/inv_sync/delete',1,1,0,1,0);",
);

$u['808_1'] = array(
	"update sys_action set action_name='账务档案' where action_id='2055000' and parent_id='2000000';",
	"update sys_action set action_id='2055100',parent_id='2055000',sort_order=1 where action_name='支付方式' and action_code='base/payment/do_list';",
	"update sys_action set action_id='2055200',parent_id='2055000',sort_order=2 where action_name='收款账户' and action_code='base/paymentaccount/account_list';"
);
$u['bug_807'] = array(
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('sap_get_store', '库存获取并调整系统库存', '', '', '0', '5', '启用后，轮询获取SAP变化库存并调整系统库存（调整单模式）', '{\"app_act\":\"sys/sap_adjust_record/download_data\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '3600', '0', 'sys', '', '0', '', '0');",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('sap_upload_record', '单据上传（包含积分）', '', '', '0', '5', '启用后，将已发货订单，已收货退单以及月度积分单上传到SAP', '{\"app_act\":\"sys/sap_sell_record/uploade_automatism\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '1800', '0', 'sys', '', '0', '', '0');",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('sap_integral_data', 'sap积分数据更新', '', '', '0', '10', '开启后，sap数据每隔一个月更新一次', '{\"app_act\":\"sys/sap_sell_record/insert_integral\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '86400', '0', 'sys', '', '0', '', '0');",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('sap_record_data', 'sap中间表数据跟新', '', '', '0', '10', '开启后，sap数据每隔一小时更新一次', '{\"app_act\":\"sys/sap_sell_record/insert_record\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '3600', '0', 'sys', '', '0', '', '0');",
    "ALTER TABLE sap_sell_record ADD COLUMN `payable_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单应付款,商品均摊总金额+运费';",
    "CREATE TABLE sap_update_time
(
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`update_time` INT(11) NOT NULL DEFAULT 0 COMMENT '更新单据时间',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='sap更新时间表';"
);
