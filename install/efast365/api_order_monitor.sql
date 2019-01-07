DROP TABLE IF EXISTS `api_order_monitor`;
CREATE TABLE `api_order_monitor` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `shop_code` varchar(100) NOT NULL,
	  `monitor_date` date NOT NULL,
	  `insert_time` datetime NOT NULL COMMENT '数据变更时间',
	  `base_order_total` int(11) NOT NULL COMMENT '系统订单数',
	  `taobao_order_total` int(11) DEFAULT NULL COMMENT '平台订单数',
	  `interval_time` char(255) DEFAULT '60' COMMENT '监控时间段',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `shop_code_monitor_time` (`shop_code`,`monitor_date`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;