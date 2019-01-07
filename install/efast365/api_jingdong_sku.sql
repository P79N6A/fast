-- ----------------------------
-- Table structure for api_jingdong_sku
-- ----------------------------
DROP TABLE IF EXISTS `api_jingdong_sku`;
CREATE TABLE `api_jingdong_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku_id` varchar(20) DEFAULT NULL COMMENT '京东SKU ID',
  `ware_id` varchar(20) DEFAULT NULL COMMENT '商品ID',
  `shop_id` varchar(10) DEFAULT NULL COMMENT '京东店铺id',
  `attributes` varchar(200) DEFAULT NULL COMMENT 'sku的销售属性组合字符串（颜色，大小，等等，可通过类目API获取某类目下的销售属性）,格式是p1:v1;p2:v2',
  `status` varchar(10) DEFAULT NULL COMMENT 'sku状态: 有效-Valid 无效-Invalid 删除-Delete',
  `stock_num` varchar(10) DEFAULT NULL COMMENT '库存',
  `jd_price` varchar(10) DEFAULT NULL COMMENT '京东价,精确到2位小数，单位元',
  `cost_price` varchar(10) DEFAULT NULL COMMENT '进货价, 精确到2位小数，单位元',
  `market_price` varchar(10) DEFAULT NULL COMMENT '市场价, 精确到2位小数，单位元',
  `outer_id` varchar(50) DEFAULT NULL COMMENT '外部id,商家设置的外部id',
  `created` varchar(20) DEFAULT NULL COMMENT 'sku创建时间时间格式：yyyy-MM-ddHH:mm:ss',
  `modified` varchar(20) DEFAULT NULL COMMENT 'sku修改时间时间格式：yyyy-MM-ddHH:mm:ss',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `color_value` varchar(20) DEFAULT NULL COMMENT '颜色对应的值',
  `size_value` varchar(20) DEFAULT NULL COMMENT '尺码对应的值',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku_id_sd` (`sku_id`,`shop_code`),
  KEY `ware_id` (`ware_id`),
  KEY `sku_id` (`sku_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

