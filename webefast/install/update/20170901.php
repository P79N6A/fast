<?php
$u['1565'] = array(
    "INSERT INTO sys_role_action VALUES (100,4020323);",
);

$u['bug_1651'] = array(
    "ALTER TABLE api_weipinhuijit_order_detail ADD COLUMN release_time datetime DEFAULT NULL COMMENT '核销时间'",
    "ALTER TABLE api_weipinhuijit_order_detail ADD COLUMN insert_time datetime DEFAULT NULL COMMENT '插入时间'",
    "INSERT INTO `sys_schedule` (`code`, `name`, `sale_channel_code`, `status`, `type`, `request`, `loop_time`, `task_type`, `task_module`) VALUES('weipinhuijit_getOccupiedOrders_cmd', '唯品会jit已成交销售订单查询','weipinhui','0', '1','{\"action\":\"api/order/weipinhuijit_getOccupiedOrders_cmd\"}', '120', '0', 'api');"
);

$u['1631']=array(
    "INSERT INTO `base_sale_channel` (`sale_channel_code`, `short_code`, `sale_channel_name`, `is_system`, `is_active`, `remark`, `lastchanged`) VALUES ('shangpai', 'shangpai', '商派', '1', '1', '', '2017-08-21 14:12:40');"
);

$u['bug_1658']=array(
	"UPDATE sys_schedule set loop_time=300 WHERE `code`='weipinhui_inv_upload_cmd';",
);
$u['1620'] = array(  //订单发票类型增加增值税发票
     "CREATE TABLE oms_sell_invoice
(
	`invoice_id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号)',
  `deal_code` varchar(200) NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)',
  `deal_code_list` varchar(500) NOT NULL DEFAULT '' COMMENT '平台交易号列表',
  `customer_code` varchar(20) NOT NULL DEFAULT '' COMMENT '会员代码',
  `buyer_name` varchar(30) DEFAULT NULL COMMENT '买家昵称',
  `receiver_name` varchar(30) DEFAULT NULL COMMENT '收货人名称',
	`company_name` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '公司名称',
	`taxpayers_code` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '纳税人代码',
	`registered_country` bigint(20) DEFAULT NULL COMMENT '国家',
  `registered_province` bigint(20) DEFAULT NULL COMMENT '省',
  `registered_city` bigint(20) DEFAULT NULL COMMENT '市',
  `registered_district` bigint(20) DEFAULT NULL COMMENT '区',
  `registered_street` bigint(20) DEFAULT NULL COMMENT '街道',
  `registered_addr` varchar(100) NOT NULL DEFAULT '' COMMENT '注册详细地址(不包含省市区)',
	`registered_address` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '注册详细地址（包含省市区）',
	`phone` varchar(128) DEFAULT '' COMMENT '注册电话',
	`bank` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '开户银行',
	`bank_account` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '开户银行账号',
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `idxu_record_code` (`sell_record_code`) USING BTREE,
  UNIQUE KEY `idxu_deal_code` (`deal_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='销售订单增值发票表';"
);

?>