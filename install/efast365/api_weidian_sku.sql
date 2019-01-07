-- ----------------------------
-- Table structure for api_weidian_sku
-- ----------------------------
DROP TABLE IF EXISTS `api_weidian_sku`;
CREATE TABLE `api_weidian_sku` (
  `sku_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` varchar(50) NOT NULL COMMENT '型号的唯一id',
  `itemid` varchar(50) NOT NULL COMMENT '商品的id',
  `title` varchar(50) DEFAULT NULL COMMENT '型号名称',
  `price` varchar(50) DEFAULT NULL COMMENT '价格信息',
  `stock` int(10) DEFAULT NULL COMMENT '属于这个Sku的商品的数量',
  `sku_merchant_codes` varchar(255) DEFAULT NULL COMMENT '商品型号编码',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`sku_id`),
  UNIQUE KEY `id` (`id`,`shop_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微店SKU列表';

