DROP TABLE IF EXISTS `api_weipinhuijit_store_out_record`;
CREATE TABLE `api_weipinhuijit_store_out_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
  `delivery_no` varchar(50) NOT NULL COMMENT '出库单号',
  `notice_record_no` varchar(50) NOT NULL COMMENT '通知单号',
  `pick_no` varchar(50) NOT NULL COMMENT '拣货单编号',
  `store_out_record_no` varchar(50) NOT NULL COMMENT '批发销货单号',
  `po_no` varchar(50) NOT NULL COMMENT 'PO单编号',
  `warehouse` varchar(50) NOT NULL COMMENT '送货仓库',
  `delivery_id` varchar(50) DEFAULT NULL COMMENT '出库单Id',
  `storage_no` varchar(50) DEFAULT NULL COMMENT '入库编号',
  `insert_time` varchar(50) DEFAULT NULL COMMENT '插入时间',
  `brand_code` varchar(100) NOT NULL COMMENT '品牌',
  `express_code` varchar(50) DEFAULT NULL COMMENT '配送方式code',
  `express` varchar(20) NOT NULL DEFAULT '' COMMENT '快递单号',
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_out_record_no` (`store_out_record_no`),
  KEY `po_no` (`po_no`),
  KEY `notice_record_no` (`notice_record_no`),
  KEY `pick_no` (`pick_no`)

) ENGINE=InnoDB  DEFAULT CHARSET=utf8;