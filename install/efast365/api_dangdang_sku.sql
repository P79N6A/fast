-- ----------------------------
-- Table structure for api_dangdang_sku
-- ----------------------------
DROP TABLE IF EXISTS `api_dangdang_sku`;
CREATE TABLE `api_dangdang_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itemID` varchar(50) DEFAULT NULL COMMENT '商品标识符',
  `subItemID` varchar(50) DEFAULT NULL COMMENT '分色分码的商品标志符',
  `outerItemID` varchar(50) DEFAULT NULL COMMENT '自定义的商品标识符(如果商品没有“企业商品标志符”，则不返回此项)',
  `unitPrice` varchar(10) DEFAULT NULL COMMENT '单价',
  `stockCount` varchar(20) DEFAULT NULL COMMENT '库存数量',
  `specialAttributeClass` varchar(255) DEFAULT NULL COMMENT '销售属性值类',
  `specialAttribute` varchar(255) DEFAULT NULL COMMENT '自定义属性名称',
  `specialAttributeSeq` varchar(255) DEFAULT NULL COMMENT '销售属性值顺序',
  `volume` varchar(20) DEFAULT NULL COMMENT '商品体积',
  `weight` varchar(20) DEFAULT NULL COMMENT '商品重量',
  `itemPic` varchar(255) DEFAULT NULL COMMENT '商品分色分码自定义图片URL(若无图，不返回此节点)',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `subItemID_sd` (`subItemID`,`shop_code`),
  KEY `subItemID` (`subItemID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
