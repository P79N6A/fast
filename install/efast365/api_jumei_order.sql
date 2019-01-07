-- ----------------------------
-- Table structure for api_jumei_order 聚美订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_jumei_order`;
CREATE TABLE `api_jumei_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oid` varchar(30) DEFAULT '' COMMENT '订单ID',
  `deal_hash_id` varchar(40) DEFAULT '' COMMENT '聚美商品hash_id',
  `sku_no` varchar(40) DEFAULT '' COMMENT '聚美商品唯一编码',
  `upc_code` varchar(40) DEFAULT '' COMMENT '商家商品编码（即商家商品的sku_no,商家ERP通过此编码确定唯一的库存单元）',
  `deal_short_name` varchar(30) DEFAULT '' COMMENT '产品名称',
  `attribute` varchar(255) DEFAULT '' COMMENT '规格型号',
  `deal_price` varchar(30) DEFAULT '' COMMENT '聚美价',
  `quantity` int(10) DEFAULT 0 COMMENT '数量',
  `settlement_price` varchar(30) DEFAULT '' COMMENT '商品折后价（将优惠总金额按比例分摊到各个商品，然后结算的到的折后价）',
  `supplier_code` varchar(30) DEFAULT '' COMMENT '商品货号',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',  
  PRIMARY KEY (`id`),
  UNIQUE KEY `oid` (`oid`,`sku_no`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='聚美订单明细';
