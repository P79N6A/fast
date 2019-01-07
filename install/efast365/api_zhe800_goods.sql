DROP TABLE IF EXISTS `api_zhe800_goods`;
CREATE TABLE `api_zhe800_goods` (
  `goods_id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(20) NOT NULL,
  `id` varchar(50) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '商品名称',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '商品副标题',
  `short_name` varchar(50) NOT NULL DEFAULT '' COMMENT '商品短标题',
  `stock` int(11) NOT NULL DEFAULT '0' COMMENT '商品库存',
  `sales_count` int(11) NOT NULL DEFAULT '0' COMMENT '商品销售',
  `org_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品原价',
  `cur_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品现价',
  `num` varchar(50) NOT NULL DEFAULT '' COMMENT '商品货号',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '商品主图',
  `shelf` varchar(50) NOT NULL DEFAULT '' COMMENT '库位',
  `place_of_dispatch` varchar(50) NOT NULL DEFAULT '' COMMENT '商品发货地',
  PRIMARY KEY (`goods_id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


