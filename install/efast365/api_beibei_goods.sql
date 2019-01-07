-- ----------------------------
-- Table structure for api_beibei_goods 贝贝商品信息
-- ----------------------------
DROP TABLE IF EXISTS `api_beibei_goods`;
CREATE TABLE `api_beibei_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `iid` varchar(50) DEFAULT NULL COMMENT '商品id',
  `mid` varchar(50) DEFAULT NULL COMMENT '专场ID',
  `title` varchar(50) DEFAULT NULL COMMENT '商品名称',
  `price` varchar(20) DEFAULT NULL COMMENT '商品价格',
  `origin_price` varchar(20) DEFAULT NULL COMMENT '原价',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `iid` (`iid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '贝贝商品信息';