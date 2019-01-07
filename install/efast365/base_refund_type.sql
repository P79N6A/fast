
DROP TABLE IF EXISTS `base_refund_type`;
CREATE TABLE `base_refund_type` (
  `refund_type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `refund_type_code` varchar(128) DEFAULT '' COMMENT '代码',
  `refund_type_name` varchar(128) DEFAULT '' COMMENT '名称',
  `is_fetch` tinyint(1) DEFAULT '0' COMMENT '0：正常 1：待处理',
  `relation_code` varchar(128) DEFAULT '0' COMMENT '关联ID',
  `status` tinyint(1) DEFAULT '1' COMMENT '0：停用 1：启用',
  `is_vouch` tinyint(1) DEFAULT '0' COMMENT '担保交易 0：不是 1：是',
  `is_cod` tinyint(1) DEFAULT '0' COMMENT '货到付款 0：款到付货 1：货到付款',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `charge` decimal(10,3) DEFAULT '0.000' COMMENT '支付手续费',
  PRIMARY KEY (`refund_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='退款方式';


