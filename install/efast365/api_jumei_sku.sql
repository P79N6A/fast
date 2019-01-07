-- ----------------------------
-- Table structure for api_jumei_sku 聚美商品sku列表
-- ----------------------------
DROP TABLE IF EXISTS `api_jumei_sku`;
CREATE TABLE `api_jumei_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `upc_code` varchar(50) DEFAULT NULL COMMENT '商家商品编码（即商家商品的sku_no,商家ERP通过此编码确定唯一的库存单元）',
  `sku_no` varchar(50) DEFAULT NULL COMMENT '聚美商品唯一编码',
  `iid` int(10) DEFAULT '0' COMMENT '商品product_id',
  `name` varchar(50) DEFAULT NULL COMMENT 'sku型号名称',
  `stock` int(10) DEFAULT 0 COMMENT 'sku锁定库存',
  `product_sn` varchar(50) DEFAULT NULL COMMENT 'sku货号',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku_no` (`sku_no`) USING BTREE,
  KEY `upc_code` (`upc_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '聚美商品sku列表';