DROP TABLE IF EXISTS `fx_running_account`;
CREATE TABLE `fx_running_account` (
  `running_account_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_code` varchar(128) DEFAULT '' COMMENT '流水号',
  `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
  `record_type` varchar(128) DEFAULT '' COMMENT '业务类型，pre_deposits：预存款；sales_settlement：销售结算;sales_refund:销售退款',
  `account_money_start` decimal(10,3) DEFAULT '0.000' COMMENT '变更前余额',
  `account_money_end` decimal(10,3) DEFAULT '0.000' COMMENT '变更后余额',
  `account_money` decimal(10,3) DEFAULT '0.000' COMMENT '金额',
  `change_time` datetime NOT NULL COMMENT '变更时间',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`running_account_id`),
  KEY `account_code` (`record_code`),
  KEY `custom_code` (`custom_code`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分销商往来流水账';