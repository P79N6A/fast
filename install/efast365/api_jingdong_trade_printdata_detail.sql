-- ----------------------------
-- Table structure for api_jingdong_trade_printdata 京东订单打印数据表
-- ----------------------------
DROP TABLE IF EXISTS `api_jingdong_trade_printdata_detail`;
CREATE TABLE `api_jingdong_trade_printdata_detail` (
  `api_print_id` int(11) NOT NULL AUTO_INCREMENT,
  `ware` varchar(50) DEFAULT '' COMMENT '',
  `ware_name` varchar(255) DEFAULT '' COMMENT '',
  `num` varchar(10) DEFAULT '' COMMENT '',
  `jd_price` varchar(40) DEFAULT '' COMMENT '',
  `price` varchar(30) DEFAULT '' COMMENT '',
  `produce_no` varchar(50) DEFAULT '' COMMENT '',
  `id` varchar(30) DEFAULT '' COMMENT '订单编号',
  PRIMARY KEY (`api_print_id`),
  UNIQUE KEY `order_id` (`id`,`ware`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='api京东订单打印数据表明细';