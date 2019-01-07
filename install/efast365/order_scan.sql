
DROP TABLE IF EXISTS `order_scan`;
CREATE TABLE `order_scan` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `order_type` int(4) DEFAULT '0' COMMENT '订单类型',
  `order_time` datetime DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单监控'

