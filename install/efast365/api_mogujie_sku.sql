-- ----------------------------
-- Table structure for api_mogujie_sku
-- ----------------------------
DROP TABLE IF EXISTS `api_mogujie_sku`;
CREATE TABLE `api_mogujie_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` varchar(50) DEFAULT NULL COMMENT '商品Id',
  `sku_id` varchar(50) DEFAULT NULL COMMENT '商品sku ID',
  `sku_code` varchar(50) DEFAULT NULL COMMENT 'sku 编码',
  `sku_price` varchar(10) DEFAULT NULL COMMENT 'sku 价格',
  `sku_prop` text COMMENT 'json-sku 属性',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  `sku_stock` varchar(50) DEFAULT NULL COMMENT 'sku库存',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku_id` (`sku_id`),
  KEY `sku_code` (`sku_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
