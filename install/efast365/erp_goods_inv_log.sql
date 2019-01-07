DROP TABLE IF EXISTS `erp_goods_inv_log`;
CREATE TABLE `erp_goods_inv_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
  `efast_store_code` varchar(20) NOT NULL DEFAULT '' COMMENT 'efast仓库代码',
  `barcode` varchar(20) NOT NULL DEFAULT '' COMMENT '商品条码',
  `sku` varchar(20) NOT NULL DEFAULT '' COMMENT '系统SKU码',
  `goods_code` varchar(20) NOT NULL,
  `spec1_code` varchar(20) NOT NULL,
  `spec2_code` varchar(20) NOT NULL,
  `prev_num` int(11) NOT NULL DEFAULT '0' COMMENT '原先库存数量',
  `after_num` tinyint(11) NOT NULL DEFAULT '0' COMMENT '更新后的库存数量',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `barcode` (`barcode`) USING BTREE,
  KEY `sku` (`sku`) USING BTREE,
  KEY `efast_store_code` (`efast_store_code`) USING BTREE,
  KEY `idx_efast_store_code_barcode` (`efast_store_code`,`barcode`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;