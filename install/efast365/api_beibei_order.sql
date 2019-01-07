-- ----------------------------
-- Table structure for api_beibei_order 贝贝订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_beibei_order`;
CREATE TABLE `api_beibei_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oid` varchar(30) DEFAULT '' COMMENT '订单ID',
  `iid` varchar(40) DEFAULT '' COMMENT '产品id',
  `sku_id` varchar(40) DEFAULT '' COMMENT 'sku id',
  `outer_id` varchar(40) DEFAULT '' COMMENT '商家编码',
  `title` varchar(30) DEFAULT '' COMMENT '产品名称',
  `price` varchar(30) DEFAULT '' COMMENT '商品价格',
  `num` varchar(50) DEFAULT '' COMMENT '数量',
  `origin_price` varchar(30) DEFAULT '' COMMENT '产品原价',
  `subtotal` varchar(30) DEFAULT '' COMMENT '小计',
  `sku_properties` varchar(255) DEFAULT '' COMMENT 'SKU属性',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',  
  PRIMARY KEY (`id`),
  KEY `oid` (`oid`,`sku_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='贝贝订单详细';
