DROP TABLE IF EXISTS `api_zhe800_sku`;
CREATE TABLE `api_zhe800_sku` (
  `api_zhe800_sku_id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(30) NOT NULL DEFAULT '' COMMENT '店铺代码',
  `id` varchar(50) NOT NULL DEFAULT '0',
  `sku_num` varchar(60) NOT NULL DEFAULT '' COMMENT 'sku_num',
  `sku_desc` varchar(60) NOT NULL DEFAULT '' COMMENT 'sku_desc',
  `stock` varchar(60) NOT NULL DEFAULT '' COMMENT '库存',
  `cur_price` varchar(60) NOT NULL DEFAULT '' COMMENT '当前价格',
  `org_price` varchar(60) NOT NULL DEFAULT '' COMMENT '原始价格',
  `shelf` varchar(60) NOT NULL DEFAULT '' COMMENT '库位',
  `seller_no` varchar(60) NOT NULL DEFAULT '' COMMENT '商家编码',
  `sku_id` varchar(32) NOT NULL,
  PRIMARY KEY (`api_zhe800_sku_id`),
  UNIQUE KEY `outer_id` (`id`,`sku_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

