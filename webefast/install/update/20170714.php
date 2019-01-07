<?php

$u['1481']=array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'deliver_record_direct_ship', 'waves_property', '扫描物流单后直接发货', 'radio', '[\"关闭\",\"开启\"]', '1', '0.00', '1-开启 0-关闭', '2016-07-07 18:56:47', '开启后，扫描验货,扫描物流单后直接发货可以编辑');",
);
$u['1483'] = array(
    "CREATE TABLE base_custom_express_freight
(
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
	`express_code` varchar(128) DEFAULT '' COMMENT '配送方式代码',
	`express_money` decimal(10,2) DEFAULT '0.00' COMMENT '运费',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`custom_code`,`express_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分销商快递运费中间表';"
);
$u['bug_1469'] = array(
    "ALTER TABLE payment_account ADD COLUMN `bank_code` varchar(50) NOT NULL COMMENT '银行账号';"
);

$u['1487'] = array("INSERT INTO `base_sale_channel` (`sale_channel_code`, `short_code`, `sale_channel_name`, `is_system`, `is_active`) VALUES ('chuizhicai', 'czc', '垂直采', '1', '1');");