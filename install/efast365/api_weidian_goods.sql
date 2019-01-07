-- ----------------------------
-- Table structure for api_weidian_goods 微店商品信息
-- ----------------------------
DROP TABLE IF EXISTS `api_weidian_goods`;
CREATE TABLE `api_weidian_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sd_id` int(10) NOT NULL COMMENT '关联的商店ID',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `itemid` varchar(50) DEFAULT NULL COMMENT '商品id',
  `item_name` varchar(50) DEFAULT NULL COMMENT '商品名称',
  `stock` int(10) DEFAULT NULL COMMENT '商品数量',
  `price` varchar(20) DEFAULT NULL COMMENT '商品价格，格式：5.00',
  `sold` int(10) DEFAULT NULL COMMENT '商品销量',
  `seller_id` varchar(50) DEFAULT NULL COMMENT '卖家id',
  `istop` int(10) DEFAULT 0 COMMENT '是否店长推荐：1-是，0-不是',
  `merchant_code` varchar(50) DEFAULT NULL COMMENT '商品编号',
  `fx_fee_rate` varchar(50) DEFAULT NULL COMMENT '分销分成比例',
  `status` varchar(50) DEFAULT NULL COMMENT '商品状态onsale ：销售中instock：已下架delete：已删除',
  `cates` varchar(255) DEFAULT NULL COMMENT '商品分类(json)',
  `imgs` varchar(255) DEFAULT NULL COMMENT '商品图(json)',
  `thumb_imgs` varchar(255) DEFAULT NULL COMMENT '商品缩略图(json)',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `itemid` (`itemid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微店商品信息';