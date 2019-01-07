DROP TABLE IF EXISTS `api_vipshop_order`;

CREATE TABLE `api_vipshop_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯品会订单明细表',
  `brand_name` varchar(50) DEFAULT NULL COMMENT '品牌名称',
  `good_name` varchar(50) NOT NULL COMMENT '商品名称',
  `size` varchar(50) NOT NULL COMMENT '尺码',
  `good_no` varchar(50) DEFAULT NULL COMMENT '货号',
  `good_sn` varchar(50) DEFAULT NULL COMMENT '条形码',
  `amount` int(11) DEFAULT NULL COMMENT '商品数量',
  `price` double(11,0) DEFAULT NULL COMMENT '单价',
  `order_sn` varchar(50) DEFAULT NULL COMMENT '订单号',
  PRIMARY KEY (`id`),
  KEY `good_name` (`good_name`) USING BTREE,
  KEY `order_sn` (`good_sn`) USING BTREE,
  KEY `price` (`price`),
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8