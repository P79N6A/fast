DROP TABLE IF EXISTS `api_order_monitor_section`;
CREATE TABLE `api_order_monitor_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
  `monitor_date` date NOT NULL COMMENT '监控的日期',
  `monitor_start_time` datetime NOT NULL COMMENT '监控的时间段-开始时间',
  `monitor_end_time` datetime DEFAULT NULL COMMENT '监控的时间段-结束时间',
  `base_order_total` int(11) NOT NULL COMMENT '系统订单数',
  `taobao_order_total` int(11) NOT NULL COMMENT '平台订单数',
  `insert_time` datetime DEFAULT NULL COMMENT '插入时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_code_monitor_start_end_time` (`monitor_start_time`,`monitor_end_time`,`shop_code`),
  KEY `monitor_date` (`monitor_date`),
  KEY `shop_code_monitor_code` (`shop_code`,`monitor_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;