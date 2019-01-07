DROP TABLE IF EXISTS `wms_b2b_order_detail`;
CREATE TABLE `wms_b2b_order_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
  `record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '订单号',
  `record_type` varchar(20) NOT NULL DEFAULT '' COMMENT '订单类型',
  `barcode` varchar(20) NOT NULL DEFAULT '' COMMENT '商品条码',
  `wms_sl` int(11) NOT NULL DEFAULT '0' COMMENT 'wms商品数量',
  `item_type` tinyint(3) DEFAULT '1' COMMENT '1是正品，0次品',
  `new_record_code` varchar(64) NOT NULL DEFAULT '' COMMENT '自动生存单号',
  `is_create` smallint(2) NOT NULL DEFAULT '0' COMMENT '单据是否创建',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_record_code_type` (`record_code`,`record_type`,`barcode`,`item_type`,`new_record_code`) USING BTREE,
  KEY `barcode` (`barcode`) USING BTREE,
  KEY `record_code` (`record_code`) USING BTREE,
  KEY `record_type` (`record_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;