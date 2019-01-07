<?php

$u['1428'] = array(
    " ALTER TABLE oms_return_package ADD COLUMN `is_exchange_goods` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否换货: 0否，1是';"
);

$u['bug_1407'] = array(
    "
        ALTER TABLE `oms_sell_record_notice_detail`
MODIFY COLUMN `sku`  varchar(128) NOT NULL DEFAULT '' COMMENT 'sku' AFTER `spec2_code`;
",
);

$u['1429'] = array(
    "INSERT INTO `base_question_label` (`question_label_code`, `question_label_name`, `is_active`, `is_sys`, `content`, `remark`) VALUES ('BELOW_NORMAL_PRICE', '低于正常售价', '0', '1', NULL, '平台订单转单时，订单低于最低售价，订单将自动设问');",
    "ALTER TABLE base_goods ADD COLUMN `min_price` decimal(20,3) DEFAULT '0.000' COMMENT '最低售价' AFTER `purchase_price`;"

);


$u['1432'] = array(
"CREATE TABLE `base_custom_address` (
  `custom_address_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `custom_code` varchar(128) NOT NULL DEFAULT '' COMMENT '分销商代码',
  `country` varchar(20) NOT NULL DEFAULT '0' COMMENT '国家',
  `province` varchar(20) NOT NULL DEFAULT '' COMMENT '省',
  `city` varchar(20) NOT NULL DEFAULT '' COMMENT '市',
  `district` varchar(20) NOT NULL DEFAULT '' COMMENT '区',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '详细地址',
  `zipcode` varchar(255) NOT NULL DEFAULT '' COMMENT '邮编',
  `tel` varchar(255) NOT NULL DEFAULT '' COMMENT '电话',
  `home_tel` varchar(255) NOT NULL DEFAULT '' COMMENT '座机',
  `is_add_time` datetime NOT NULL COMMENT '新建时间',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `is_default` tinyint(4) NOT NULL  COMMENT '是否默认,0 否;1 是',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`custom_address_id`),
  KEY `custom_code` (`custom_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分销商收货地址';",
"INSERT INTO base_custom_address (
	custom_code,
	country,
	province,
	city,
	district,
	address,
	home_tel,
	tel,
	is_add_time,
	`name`,
	is_default
) SELECT
	custom_code,
	1 AS country,
	province,
	city,
	district,
	address,
	tel,
	mobile,
	NOW() AS is_add_time,
	contact_person AS `name`,
	1 AS is_default FROM base_custom WHERE province <> '';",
"CREATE TABLE base_custom_address_log
(
	`log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`custom_code` varchar(128) DEFAULT '' COMMENT '客户代码',
	`user_code` varchar(30) NOT NULL COMMENT '员工代码',
  `user_name` varchar(30) NOT NULL COMMENT '员工名称',
  `action_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作名称',
  `action_note` mediumtext NOT NULL DEFAULT '' COMMENT '操作描述',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分销商收货地址日志';",
    "ALTER TABLE base_custom ADD COLUMN `country` varchar(20) NOT NULL DEFAULT '0' COMMENT '国家' AFTER `create_time`;",
    "UPDATE base_custom SET `country` = '1';",
    "UPDATE fx_purchaser_record SET `country` = '1';",
    "ALTER TABLE base_custom_address ADD UNIQUE KEY _idx(custom_code, country, province, city, district, address);"

);

$u['1116'] = array(
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) 
    VALUES ('upload_hanlder_tickets', '上传工单处理', '', 'sys', '1', '10', '', '{\"app_act\":\"qianniu/upload_data\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '60', '0', 'sys', '', '0', NULL, '0');",
        "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) 
    VALUES ('upload_hanlder_tickets', '工单处理', '', 'sys', '1', '10', '', '{\"app_act\":\"qianniu/exec_ticket_all\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '60', '0', 'sys', '', '0', NULL, '0');",
);

$u['bug_1442'] = array(
    "ALTER TABLE report_alipay ADD UNIQUE KEY `index_key` (`shop_code`,`account_item`,`is_account_in`,`account_month_ym`);",
);

$u['1421'] = array("INSERT INTO `base_sale_channel` (`sale_channel_code`, `short_code`, `sale_channel_name`, `is_system`, `is_active`) VALUES ('weifenxiao', 'wfx', '微分销（启博）', '1', '1');");
