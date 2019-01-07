DROP TABLE IF EXISTS `api_data_monitor`;
CREATE TABLE `api_data_monitor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(50) NOT NULL COMMENT '店铺代码',
  `action_code` varchar(50) NOT NULL COMMENT '行为代码，比如订单下载等',
  `start_time` varchar(20) NOT NULL COMMENT '开始时间',
  `end_time` varchar(20) NOT NULL COMMENT '结束时间',
  `count_for_online` int(11) NOT NULL DEFAULT '0' COMMENT '线上数量',
  `count_for_offine` int(11) NOT NULL DEFAULT '0' COMMENT '本地数量',
  `msg` varchar(500) DEFAULT NULL COMMENT '备注信息',
  PRIMARY KEY (`id`),
  KEY `key_action_code` (`shop_code`,`action_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
