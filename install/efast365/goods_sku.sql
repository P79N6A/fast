
DROP TABLE IF EXISTS `goods_sku`;
CREATE TABLE `goods_sku` (
  `sku_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec1_name` varchar(128) DEFAULT NULL,
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `spec2_name` varchar(128) DEFAULT NULL,
  `sku` varchar(30) DEFAULT '' COMMENT 'sku',
  `barcode` varchar(128) DEFAULT NULL,
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `price` decimal(10,3) DEFAULT '0.000' COMMENT '吊牌价',
  `remark` varchar(100) DEFAULT '' COMMENT '备注',
  `gb_code` varchar(30) DEFAULT NULL COMMENT '国标码',
  `weight` decimal(10,3) DEFAULT '0.000' COMMENT '重量',
  PRIMARY KEY (`sku_id`),
  UNIQUE KEY `idxu_gs` (`goods_code`,`spec1_code`,`spec2_code`) USING BTREE,
  UNIQUE KEY `idxu_sku` (`sku`) USING BTREE,
  UNIQUE KEY `barcode` (`barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品sku';
