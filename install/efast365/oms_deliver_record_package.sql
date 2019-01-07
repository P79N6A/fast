DROP TABLE IF EXISTS `oms_deliver_record_package`;
CREATE TABLE `oms_deliver_record_package` (
  `package_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(128) NOT NULL COMMENT '订单编号',
  `express_code` varchar(64) DEFAULT '0' COMMENT '配送方式CODE',
  `express_no` varchar(128) DEFAULT '' COMMENT '快递单号',
  `express_data` text COMMENT '云栈获取数据',
  `package_no` tinyint(3) DEFAULT '0',
  `waves_record_id` int(11) DEFAULT '0',
  `is_weigh` int(4) NOT NULL DEFAULT '0' COMMENT '称重 0未称重 1称重',
  `real_weigh` decimal(10,3) DEFAULT '0.000' COMMENT '实际总重量',
  `weigh_express_money` decimal(10,2) DEFAULT '0.00' COMMENT '称重后计算的快递费用',
  `weigh_time` datetime DEFAULT NULL COMMENT '称重时间',
  `weigh_person` varchar(20) NOT NULL DEFAULT '' COMMENT '称重人',
  PRIMARY KEY (`package_record_id`),
  UNIQUE KEY `_key` (`sell_record_code`,`package_no`,`waves_record_id`) USING BTREE,
  KEY `_waves_record_id` (`waves_record_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='发货订单包裹';