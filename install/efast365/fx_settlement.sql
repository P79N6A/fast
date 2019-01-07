DROP TABLE IF EXISTS `fx_settlement`;
CREATE TABLE `fx_settlement` (
  `settlement_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
  `record_type` varchar(128) DEFAULT '' COMMENT '业务类型，pre_deposits：预存款；sales_settlement：销售结算;sales_refund:销售退款',
  `advance_payment` decimal(10,3) DEFAULT '0.000' COMMENT '预扣款',
  `status` tinyint(3) DEFAULT '0' COMMENT '金额状态，0:预扣款；1:已结算',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `relation_code` varchar(128) DEFAULT '' COMMENT '关联单据编号',
  PRIMARY KEY (`settlement_id`),
  UNIQUE KEY `relation_code` (`relation_code`),
  KEY `custom_code` (`custom_code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='分销商结算单';