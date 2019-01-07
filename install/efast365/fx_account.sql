DROP TABLE IF EXISTS `fx_account`;
CREATE TABLE `fx_account` (
  `account_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_code` varchar(128) DEFAULT '' COMMENT '流水号',
  `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
  `pay_type` varchar(128) DEFAULT '' COMMENT '支付方式',
  `account_money` decimal(10,3) DEFAULT '0.000' COMMENT '充值金额',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `confirm_time` datetime NOT NULL COMMENT '到款确认时间',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(3) DEFAULT '0' COMMENT '是否确认',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `account_code` (`account_code`) USING BTREE,
  KEY `custom_code` (`custom_code`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分销商预存款';
