DROP TABLE IF EXISTS `wms_oms_order`;
CREATE TABLE `wms_oms_order` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
  `record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '订单号',
  `record_type` varchar(20) NOT NULL DEFAULT '' COMMENT '订单类型',
  `barcode` varchar(20) NOT NULL DEFAULT '' COMMENT '商品条码',
  `efast_sl` int(11) NOT NULL DEFAULT '-1' COMMENT 'efast商品数量',
  `wms_sl` int(11) NOT NULL DEFAULT '-1' COMMENT 'wms商品数量',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_record_code_type` (`record_code`,`record_type`,`barcode`) USING BTREE,
  KEY `barcode` (`barcode`) USING BTREE,
  KEY `record_code` (`record_code`) USING BTREE,
  KEY `record_type` (`record_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;