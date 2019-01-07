DROP TABLE IF EXISTS `api_taobao_alipay`;
CREATE TABLE `api_taobao_alipay` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(50) DEFAULT '' COMMENT '店铺nick',
  `balance` varchar(50) DEFAULT '' COMMENT '100.00  当时支付宝账户余额',
  `memo` text COMMENT ' hello world 账号备注',
  `alipay_order_no` varchar(50) DEFAULT '' COMMENT '2014081021001001540010396144  支付宝订单号',
  `opt_user_id` varchar(50) DEFAULT '' COMMENT '20880063000888880133 对方的支付宝ID',
  `merchant_order_no` varchar(50) DEFAULT '' COMMENT 'T200P765216671818695  商户订单号',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '2014-08-20 20:40:03 创建时间',
  `self_user_id` varchar(32) DEFAULT '' COMMENT ' 20880063000888880122 自己的支付宝ID',
  `business_type` varchar(50) DEFAULT '' COMMENT 'PAYMENT 子业务类型',
  `out_amount` decimal(11,3) unsigned DEFAULT '0.000' COMMENT ' 50.00 支出金额',
  `type` varchar(50) DEFAULT '' COMMENT ' PAYMENT 账务类型',
  `in_amount` decimal(11,3) DEFAULT '0.000' COMMENT '50.00 收入金额',
  `first_insert_time` datetime DEFAULT NULL COMMENT '第一次插入时间,数据在本平台的插入时间',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deal_code` varchar(50) DEFAULT '' COMMENT '淘宝交易号，处理后',
  `account_item` varchar(20) DEFAULT '' COMMENT '会计科目',
  `account_month` date NOT NULL DEFAULT '0000-00-00' COMMENT '财务账期',
  `check_accounts_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '核销状态 0未对账 10已核销 20部分核销 30虚拟核消  40人工核销 50核销失败',
  `check_accounts_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '核销时间',
  `check_accounts_user_code` varchar(20) NOT NULL DEFAULT '' COMMENT '核销人',
  `account_month_ym` varchar(7) NOT NULL DEFAULT '0000-00' COMMENT '财务账期--仅年月',
  `sell_month_ym` varchar(7) NOT NULL DEFAULT '0000-00' COMMENT '业务账期--仅年月',
  `account_item_code` varchar(20) DEFAULT '' COMMENT '会计科目编号',
  `check_accounts_msg` varchar(255) DEFAULT '' COMMENT '核销备注',
  `is_refresh` tinyint(2) NOT NULL DEFAULT '0' COMMENT '维护零售结算汇总的各类金额数据',
  PRIMARY KEY (`aid`),
  UNIQUE KEY `alipay_order_no` (`alipay_order_no`,`merchant_order_no`,`balance`),
  KEY `shop_code` (`shop_code`) USING BTREE,
  KEY `idx_account_item` (`account_item`),
  KEY `idx_account_momth` (`shop_code`,`check_accounts_status`,`account_month_ym`,`sell_month_ym`),
  KEY `deal_code_create_time` (`deal_code`,`create_time`),
  KEY `is_refresh` (`is_refresh`),
  KEY `idx_account_item_code` (`account_item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='淘宝支付宝交易记录明细';





