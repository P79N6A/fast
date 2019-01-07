-- ----------------------------
-- Table structure for api_beibei_sku 贝贝商品sku列表
-- ----------------------------
DROP TABLE IF EXISTS `api_beibei_sku`;
CREATE TABLE `api_beibei_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `outer_id` varchar(50) DEFAULT NULL COMMENT '商家编码',
  `iid` int(10) DEFAULT '0' COMMENT '商品id',
  `sku_properties` varchar(50) DEFAULT NULL COMMENT 'sku属性',
  `num` int(10) DEFAULT 0 COMMENT '现可售库存',
  `sold_num` int(10) DEFAULT 0 COMMENT '已卖数量',
  `hold_num` int(10) DEFAULT 0 COMMENT '已下单未付款所占用的数量',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  KEY `outer_id` (`outer_id`) USING BTREE,
  KEY `iid` (`iid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '贝贝商品sku列表';