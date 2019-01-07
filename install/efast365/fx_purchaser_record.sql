DROP TABLE IF EXISTS `fx_purchaser_record`;
CREATE TABLE `fx_purchaser_record` (
  `purchaser_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_code` varchar(64) DEFAULT '' COMMENT '单据编号',
  `init_code` varchar(128) DEFAULT '' COMMENT '原单号',
  `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
  `store_code` varchar(128) DEFAULT '' COMMENT '仓库代码',
  `is_check` int(4) DEFAULT '0' COMMENT '0未确认  1确认',
  `record_time` date DEFAULT '0000-00-00' COMMENT '业务时间',
  `order_time` datetime DEFAULT NULL COMMENT '下单日期',
  `num` int(11) DEFAULT '0' COMMENT '计划采购数',
  `finish_num` int(11) DEFAULT '0' COMMENT '实际入库数',
  `sum_money` decimal(20,3) DEFAULT '0.000' COMMENT '总金额',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `is_add_person` varchar(64) DEFAULT '' COMMENT '添加人',
  `express_code` varchar(20) NOT NULL DEFAULT '' COMMENT '配送方式CODE',
  `express_no` varchar(40) NOT NULL DEFAULT '' COMMENT '快递单号',
  `express_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
  `is_settlement` tinyint(3) DEFAULT '0' COMMENT '分销结算，1:已结算，0:未结算',
  `is_deliver` tinyint(3) DEFAULT '0' COMMENT '出库，1:已出库，0:未出库',
  `deliver_time` datetime DEFAULT NULL COMMENT '出库日期',
  `country` bigint(20) DEFAULT NULL,
  `province` bigint(20) DEFAULT NULL,
  `city` bigint(20) DEFAULT NULL,
  `district` bigint(20) DEFAULT NULL,
  `street` bigint(20) DEFAULT NULL,
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址(不包含省市区)',
  `contact_person` varchar(128) DEFAULT '' COMMENT '联系人',
  `mobile` varchar(128) DEFAULT '' COMMENT '手机',
  `relation_code` varchar(128) DEFAULT '' COMMENT '关联单号',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',                  
  PRIMARY KEY (`purchaser_record_id`),
  UNIQUE KEY `_key` (`record_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='经销采购订单';