-- ----------------------------
-- Table structure for api_jingdong_order 京东订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_jingdong_order`;
CREATE TABLE `api_jingdong_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku_id` varchar(30) DEFAULT '' COMMENT '京东内部SKU的ID',
  `ware_id` varchar(30) DEFAULT '' COMMENT '京东内部商品ID',
  `jd_price` varchar(30) DEFAULT '' COMMENT 'SKU的京东价',
  `sku_name` varchar(255) DEFAULT '' COMMENT '商品的名称+SKU规格',
  `product_no` varchar(30) DEFAULT '' COMMENT '商品货号',
  `gift_point` varchar(30) DEFAULT '' COMMENT '赠送积分',
  `outer_sku_id` varchar(40) DEFAULT '' COMMENT 'SKU外部ID',
  `item_total` varchar(30) DEFAULT '' COMMENT '数量',
  `order_id` varchar(30) DEFAULT '' COMMENT '订单编号',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group` (`order_id`,`sku_id`) USING BTREE,
  KEY `shop_code` (`shop_code`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
