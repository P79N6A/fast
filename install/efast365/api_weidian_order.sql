-- ----------------------------
-- Table structure for api_weidian_order 微店订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_weidian_order`;
CREATE TABLE `api_weidian_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '',
  `order_id` varchar(50) DEFAULT NULL COMMENT '交易编号',
  `item_id` varchar(50) NOT NULL COMMENT '商品数字编号',
  `price` varchar(20) DEFAULT NULL COMMENT '商品价格',
  `quantity` varchar(50) DEFAULT NULL COMMENT '商品购买数量',
  `total_price` varchar(50) DEFAULT NULL COMMENT '商品对应总价',
  `sku_id` varchar(50) DEFAULT NULL COMMENT 'Sku的ID',
  `item_name` varchar(50) DEFAULT NULL COMMENT '商品标题',
  `img` varchar(255) DEFAULT NULL  COMMENT '商品主图片地址',
  `url` varchar(255) DEFAULT NULL  COMMENT '商品url',
  `fx_fee_rate` varchar(50) DEFAULT NULL COMMENT '分销分成比例',
  `merchant_code` varchar(50) DEFAULT NULL COMMENT '商品编号',
  `sku_merchant_codes` varchar(255) DEFAULT NULL COMMENT '商品型号编码',
  `sku_title` varchar(50) DEFAULT NULL COMMENT '型号名称',
  `shop_code` varchar(50) DEFAULT NULL COMMENT '商店代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`,`sku_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='微店订单明细';
