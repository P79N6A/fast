-- ----------------------------
-- Table structure for api_mogujie_goods
-- ----------------------------
DROP TABLE IF EXISTS `api_mogujie_goods`;
CREATE TABLE `api_mogujie_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` varchar(50) DEFAULT NULL COMMENT '商品Id',
  `item_name` varchar(50) DEFAULT NULL COMMENT '商品名',
  `item_code` varchar(50) DEFAULT NULL COMMENT '商品编码',
  `item_price` varchar(50) DEFAULT NULL COMMENT '商品价格,单位分',
  `item_stock` varchar(100) DEFAULT NULL COMMENT '商品库存',
  `item_status` varchar(10) DEFAULT NULL COMMENT '商品状态,暂时不可用,后续补充',
  `item_isShelf` varchar(10) DEFAULT NULL COMMENT '商品是否下架,0为上架,1为下架',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id_sd` (`item_id`,`shop_code`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

