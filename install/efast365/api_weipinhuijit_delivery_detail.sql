DROP TABLE IF EXISTS `api_weipinhuijit_delivery_detail`;
CREATE TABLE `api_weipinhuijit_delivery_detail` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `pid` int(11) NOT NULL,
	  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
	  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
	  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
	  `sku` varchar(255) DEFAULT NULL COMMENT 'sku',
	  `barcode` varchar(255) DEFAULT NULL COMMENT '条形码',
	  `record_code` varchar(100) DEFAULT NULL COMMENT '批发销货单号',
	  `box_no` varchar(100) DEFAULT NULL COMMENT '供应商箱号',
	  `pick_no` varchar(100) DEFAULT NULL COMMENT '拣货单号',
	  `amount` int(10) DEFAULT NULL COMMENT '商品数量',
	  `vendor_type` varchar(100) DEFAULT NULL COMMENT '供应商类型： COMMON：普通 3pl：3PL',
	  `delivery_id` varchar(50) DEFAULT NULL COMMENT '出库单Id',
	  PRIMARY KEY (`id`),
	  KEY `record_code` (`record_code`),
	  KEY `pick_no` (`pick_no`),
	  KEY `goods_code` (`goods_code`),
	  KEY `barcode` (`barcode`),
	  KEY `sku` (`sku`),
	  KEY `pid` (`pid`),
	  KEY `delivery_id` (`delivery_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;