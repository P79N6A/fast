DROP TABLE IF EXISTS `api_weipinhuijit_pick_goods`;
CREATE TABLE `api_weipinhuijit_pick_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pick_no` varchar(150) NOT NULL COMMENT '拣货单编号',
  `po_no` varchar(150) NOT NULL COMMENT 'PO单编号',
  `stock` int(10) NOT NULL COMMENT '商品拣货数量',
  `notice_stock` int(10) DEFAULT '0' COMMENT '通知数',
  `delivery_stock` int(10) DEFAULT '0' COMMENT '发货数',
  `barcode` varchar(150) NOT NULL COMMENT '商品条码',
  `sku` varchar(150) NOT NULL COMMENT '系统sku',
  `art_no` varchar(150) NOT NULL COMMENT '货号',
  `product_name` varchar(150) NOT NULL COMMENT '商品名称',
  `size` varchar(150) NOT NULL COMMENT '尺码',
  `actual_unit_price` varchar(10) DEFAULT NULL COMMENT '供货价（不含税）',
  `actual_market_price` varchar(10) DEFAULT NULL COMMENT '供货价（含税）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pick_no_barcode` (`pick_no`,`barcode`),
  KEY `pick_no` (`pick_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;