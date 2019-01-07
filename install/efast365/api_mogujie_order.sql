-- ----------------------------
-- Table structure for api_mogujie_order 蘑菇街订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_mogujie_order`;
CREATE TABLE `api_mogujie_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` varchar(30) DEFAULT '' COMMENT '交易编号(交易ID)',
  `oid` varchar(40) DEFAULT '' COMMENT '子订单编号(订单ID)',
  `iid` varchar(30) DEFAULT '' COMMENT '商品ID',
  `iurl` varchar(100) DEFAULT '' COMMENT '商品ID的加密字符串',
  `title` varchar(100) DEFAULT '' COMMENT '商品名称',
  `image` varchar(255) DEFAULT '' COMMENT 'SKU图片链接',
  `sku_id` varchar(30) DEFAULT '' COMMENT '商品的最小库存单位Sku的id',
  `sku_bn` varchar(30) DEFAULT '' COMMENT 'SKU编码',
  `sku_properties` varchar(255) DEFAULT '' COMMENT 'SKU的值。如:机身颜色:黑色; 手机套餐:官方标配',
  `items_num` varchar(255) DEFAULT '' COMMENT '订单下商品数量。取值范围:大 于零的整数',
  `total_order_fee` varchar(30) DEFAULT '' COMMENT '订单金额(单价 x 数量)',
  `discount_fee` varchar(30) DEFAULT '' COMMENT '优惠金额',
  `sale_price` varchar(30) DEFAULT '' COMMENT '子订单销售金额',
  `is_oversold` varchar(30) DEFAULT '' COMMENT '是否超卖,true超卖;false正 常。',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',  
  PRIMARY KEY (`id`),
  UNIQUE KEY `tid_oid` (`tid`,`oid`) USING BTREE,
  KEY `shop_code` (`shop_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
