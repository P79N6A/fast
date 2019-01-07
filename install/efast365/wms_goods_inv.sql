DROP TABLE IF EXISTS `wms_goods_inv`;
CREATE TABLE `wms_goods_inv` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
  `efast_store_code` varchar(20) NOT NULL DEFAULT '' COMMENT 'efast仓库代码',
  `barcode` varchar(20) NOT NULL DEFAULT '' COMMENT '商品条码',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '商品数量',
  `is_sync` tinyint(11) NOT NULL DEFAULT '0' COMMENT '是否要同步到EFAST库存 1是需要同步 0是无需同步',
  `is_success` tinyint(11) NOT NULL DEFAULT '0' COMMENT '是否同步成功 1为成功 0是不成功',
  `sync_err` varchar(20) NOT NULL DEFAULT '' COMMENT '同步失败原因',
  `sync_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '库存同步到efast的时间',
  `down_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '下载库存时间',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_efast_store_code_barcode` (`efast_store_code`,`barcode`) USING BTREE,
  KEY `barcode` (`barcode`) USING BTREE,
  KEY `efast_store_code` (`efast_store_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;