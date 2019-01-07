
DROP TABLE IF EXISTS `api_bserp_item_quantity`;
CREATE TABLE `api_bserp_item_quantity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
  `efast_store_code` varchar(50) NOT NULL COMMENT 'efast仓库代码',
  `CKDM` varchar(50) NOT NULL COMMENT '仓库代码',
  `KWDM` varchar(50) NOT NULL COMMENT '库位代码',
  `SPDM` varchar(50) NOT NULL COMMENT '商品代码',
  `GG1DM` varchar(50) NOT NULL COMMENT '颜色代码',
  `GG2DM` varchar(50) NOT NULL COMMENT '尺码代码',
  `SL` int(8) NOT NULL DEFAULT '0' COMMENT '数量',
  `SL1` int(8) DEFAULT '0' COMMENT '数量1',
  `IDRank` varchar(5) DEFAULT NULL,
  `updated` datetime NOT NULL COMMENT '最后更新时间 ',
  `efast_update` datetime DEFAULT NULL COMMENT 'efast库存更新时间',
  `update_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '库存更新状态',
  `sku` varchar(255) DEFAULT '' COMMENT 'sku',
  `barcode` varchar(255) DEFAULT '' COMMENT 'barcode',
  PRIMARY KEY (`id`),
  KEY `efast_store_code` (`efast_store_code`),
  KEY `SPDM` (`SPDM`),
  KEY `CKDM` (`CKDM`),
  UNIQUE KEY `ckdm_spdm_gg1dm_gg2dm` (`erp_config_id`,`CKDM`,`SPDM`,`GG1DM`,`GG2DM`),
  KEY `erp_config_id` (`erp_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
