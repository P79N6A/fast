-- ----------------------------
-- Table structure for api_weimob_sku 微盟商品sku列表
-- ----------------------------
DROP TABLE IF EXISTS `api_weimob_sku`;
CREATE TABLE `api_weimob_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spu_code` varchar(50) DEFAULT NULL COMMENT '该SKU所属商品编码',
  `sku_code` varchar(50) DEFAULT NULL COMMENT '该SKU唯一标识码',
  `sku_name` varchar(50) DEFAULT NULL COMMENT 'SKU名称',
  `sku_attrs` varchar(255) DEFAULT NULL COMMENT 'SKU属性列表（json）',
  `sale_price` varchar(20) DEFAULT NULL COMMENT '该SKU的销售价格',
  `market_price` varchar(20) DEFAULT NULL COMMENT '该SKU的市场价',
  `weight` varchar(20) DEFAULT NULL COMMENT '重量（克）',
  `volume` varchar(20) DEFAULT NULL COMMENT '',
  `inventory` int(10) DEFAULT NULL COMMENT '属于这个Sku的商品的数量',
  `is_onsale` varchar(20) DEFAULT 'true' COMMENT '默认为在售',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `spu_code` (`spu_code`,`sku_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微盟商品sku列表';