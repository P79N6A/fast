-- ----------------------------
-- Table structure for api_paipai_refund 拍拍退单列表
-- ----------------------------
DROP TABLE IF EXISTS `api_paipai_refund`;
CREATE TABLE `api_paipai_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '',
  `dealCode` varchar(50) NOT NULL COMMENT '订单编码',
  `dealDetailLink` varchar(100) DEFAULT '' COMMENT '订单的详情链接',
  `shop_code` varchar(50) DEFAULT '' COMMENT '商店代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `dealCode` (`dealCode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='拍拍退单列表';
