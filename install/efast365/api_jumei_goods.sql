-- ----------------------------
-- Table structure for api_jumei_goods 聚美商品信息
-- ----------------------------
DROP TABLE IF EXISTS `api_jumei_goods`;
CREATE TABLE `api_jumei_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `product_id` varchar(50) DEFAULT NULL COMMENT '商品id',
  `name` varchar(50) DEFAULT NULL COMMENT '商品名称',
  `title` varchar(50) DEFAULT NULL COMMENT '商品标题',
  `brand_label` varchar(50) DEFAULT NULL COMMENT '商品品牌名称',
  `original_price` varchar(20) DEFAULT NULL COMMENT '吊牌价',
  `discounted_price` varchar(20) DEFAULT NULL COMMENT '聚美价',
  `hash_id` varchar(64) DEFAULT NULL COMMENT '聚美dealid(每次专场同一个商品的dealid都不一样)',
  `start_time` datetime DEFAULT NULL COMMENT '商品开售时间',
  `end_time` datetime DEFAULT NULL COMMENT '商品结束时间',
  `category` varchar(64) DEFAULT NULL COMMENT '商品分类信息',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '聚美商品信息';