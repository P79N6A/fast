DROP TABLE IF EXISTS `goods_combo_barcode`;
CREATE TABLE `goods_combo_barcode` (
  `goods_combo_barcode_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` VARCHAR(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_code` VARCHAR(64) DEFAULT '' COMMENT '尺码代码',
  `sku` VARCHAR(128) DEFAULT '' COMMENT 'sku',
  `price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `barcode` VARCHAR(255) DEFAULT '' COMMENT '条码',
  `add_time` DATETIME DEFAULT NULL COMMENT '添加时间',
  `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_combo_barcode_id`),
  UNIQUE KEY `goods_code_spec` (`goods_code`,`spec1_code`,`spec2_code`) USING BTREE,
  UNIQUE KEY `barcode` (`barcode`) USING BTREE,
  UNIQUE KEY `sku` (`sku`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='商品套餐条码';