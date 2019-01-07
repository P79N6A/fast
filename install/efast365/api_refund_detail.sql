DROP TABLE IF EXISTS `api_refund_detail`;
CREATE TABLE `api_refund_detail` (
  `detail_id` int auto_increment,
  `refund_id` varchar(30) COMMENT '退款单号',
  `tid` varchar(30) COMMENT '交易号',
  `oid` varchar(30) COMMENT '子订单号。如果是单笔交易oid会等于tid',
  `goods_code` varchar(30) COMMENT '平台商品外部编码',
  `title` varchar(100) COMMENT '平台商品标题/名称',
  `price` float(7,2) COMMENT '平台商品价格',
  `num` int unsigned COMMENT '平台退货数量',
  `refund_price` float(7,2) COMMENT '平台商品退还金额',
  `sku_properties_name` varchar(50) COMMENT '平台子订单SKU的值,淘宝平台：sku_properties_name',
  `goods_barcode` varchar(20) COMMENT '平台SKU外部编码,淘宝平台：outer_iid',
  `sku_id` varchar(30) DEFAULT NULL COMMENT 'sku_id',
  PRIMARY KEY (`detail_id`),
  UNIQUE KEY `refund_id_sku_id` (`refund_id`,`sku_id`),
  KEY `refund_id` (`refund_id`),
  KEY(`tid`),
  KEY(`oid`),
  KEY(`goods_code`),
  KEY(`goods_barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '退单明细';