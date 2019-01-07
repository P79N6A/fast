-- ----------------------------
-- Table structure for api_weimob_goods 微盟商品信息
-- ----------------------------
DROP TABLE IF EXISTS `api_weimob_goods`;
CREATE TABLE `api_weimob_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spu_name` varchar(50) DEFAULT NULL COMMENT '商品标题',
  `spu_code` varchar(50) DEFAULT NULL COMMENT '商品编号',
  `category_id` varchar(20) DEFAULT NULL COMMENT '',
  `shop_code` varchar(30) DEFAULT NULL COMMENT 'efast商店代码',
  `default_img` varchar(255) DEFAULT NULL COMMENT '',
  `classify_ids` varchar(50) DEFAULT NULL COMMENT '',
  `tag_ids` varchar(50) DEFAULT NULL COMMENT '',
  `inventory` int(32) DEFAULT 0 COMMENT '商品库存',
  `is_spec` varchar(20) DEFAULT 0 COMMENT '',
  `is_virtual` int(32) DEFAULT 0 COMMENT '',
  `description` varchar(255) DEFAULT NULL COMMENT '商品描述',
  `buy_maxnum` int(32) DEFAULT 0 COMMENT '',
  `freight_type` int(32) DEFAULT 0 COMMENT '',
  `freight_price_count` int(32) DEFAULT 1 COMMENT '1卖家承担运费 2粉丝承担运费统一运费金额 3粉丝承担运费使用运费模板',
  `unify_freight_type` varchar(50) DEFAULT NULL COMMENT '1快递 2EMS 4平邮，可以使用或运算',
  `express_price` varchar(20) DEFAULT NULL COMMENT '',
  `ems_price` varchar(20) DEFAULT NULL COMMENT '',
  `post_price` varchar(20) DEFAULT NULL COMMENT '',
  `freight_template_id` int(64) DEFAULT 0 COMMENT '',
  `freight_price_type` int(32) DEFAULT 0 COMMENT '',
  `deliver_address_id` int(32) DEFAULT 0 COMMENT '',
  `is_recommend` varchar(20) DEFAULT NULL COMMENT '是否在线支付',
  `is_onsale` varchar(20) DEFAULT NULL COMMENT '是否在线支付',
  `low_sellprice` varchar(20) DEFAULT NULL COMMENT '',
  `high_sellprice` varchar(20) DEFAULT NULL COMMENT '',
  `low_marketprice` varchar(20) DEFAULT NULL COMMENT '',
  `high_marketprice` varchar(20) DEFAULT NULL COMMENT '',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `spu_code` (`spu_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微盟商品信息';