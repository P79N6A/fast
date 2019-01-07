<?php

$u = array();

$u['FSF-1642'] = array(
		"update sys_schedule set loop_time=3600 where code='goods_download_cmd';",
		"update sys_schedule set loop_time=3600 where code='fx_goods_download_cmd';",
);
$u['FSF-1666'] = array(
	"ALTER TABLE api_taobao_alipay ADD COLUMN `account_item_code` varchar(20) DEFAULT '' COMMENT '会计科目编号';",
	"ALTER TABLE api_taobao_alipay ADD COLUMN `check_accounts_msg` varchar(255) DEFAULT '' COMMENT '核销备注';",
	"ALTER TABLE api_taobao_alipay DROP KEY alipay_order_no;",
	"ALTER TABLE api_taobao_alipay ADD UNIQUE KEY `alipay_order_no` (`alipay_order_no`,`merchant_order_no`,`balance`);",
	"ALTER TABLE api_taobao_alipay MODIFY `out_amount` decimal(11,3) unsigned DEFAULT '0.000' COMMENT ' 50.00 支出金额';",
	"ALTER TABLE api_taobao_alipay MODIFY `in_amount` decimal(11,3) DEFAULT '0.000' COMMENT '50.00 收入金额';",
	"delete from alipay_account_item",
	"INSERT INTO `alipay_account_item` VALUES ('1', '001', '交易收款', '1', '2015-09-01 08:02:10');",
	"INSERT INTO `alipay_account_item` VALUES ('2', '002', '天猫佣金退款', '1', '2015-09-01 08:05:26');",
	"INSERT INTO `alipay_account_item` VALUES ('3', '003', '代扣交易退回积分', '1', '2015-09-01 08:05:27');",
	"INSERT INTO `alipay_account_item` VALUES ('4', '004', '淘宝客佣金退款', '1', '2015-09-01 08:05:27');",
	"INSERT INTO `alipay_account_item` VALUES ('5', '005', '信用卡支付服务费退款', '1', '2015-09-01 08:05:29');",
	"INSERT INTO `alipay_account_item` VALUES ('6', '101', '天猫佣金', '2', '2015-09-01 08:06:30');",
	"INSERT INTO `alipay_account_item` VALUES ('7', '102', '代扣返点积分', '2', '2015-09-01 08:06:30');",
	"INSERT INTO `alipay_account_item` VALUES ('8', '103', '淘宝客佣金代扣款', '2', '2015-09-01 08:06:31');",
	"INSERT INTO `alipay_account_item` VALUES ('9', '104', '信用卡支付服务费', '2', '2015-09-01 08:06:32');",
	"INSERT INTO `alipay_account_item` VALUES ('10','105', '售后维权扣款', '2', '2015-09-01 08:06:33');",
	"INSERT INTO `alipay_account_item` VALUES ('11','106', '分销退款', '2', '2015-09-01 08:06:33');",
	"INSERT INTO `alipay_account_item` VALUES ('12','107', '商家保证金理赔', '2', '2015-09-01 08:06:33');",
	"INSERT INTO `sys_action` VALUES ('9030300','9030000','url','支付宝核销查询','acc/api_taobao_alipay/search_list','3','1','0','1','0');",
		
);
