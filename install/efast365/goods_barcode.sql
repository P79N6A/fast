DROP TABLE IF EXISTS `goods_barcode`;
CREATE TABLE `goods_barcode` (
  `barcode_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) DEFAULT '0' COMMENT 'goods_id',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_id` int(11) DEFAULT '0' COMMENT 'color_id',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_id` int(11) DEFAULT '0' COMMENT 'size_id',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `barcode_rule_id` int(11) DEFAULT '0' COMMENT '生成规则id',
  `barcode` varchar(255) DEFAULT '' COMMENT '条码',
  `gb_code` varchar(128) DEFAULT '' COMMENT '国标码',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `serial_num` varchar(255) DEFAULT '' COMMENT '流水号',
  `serial_num_length` int(8) DEFAULT '0' COMMENT '流水号长度',
  `barcode_rule_content` int(4) DEFAULT NULL COMMENT '规则内容',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`barcode_id`),
  UNIQUE KEY `barcode` (`barcode`),
  UNIQUE KEY `sku` (`sku`),
  UNIQUE KEY `goods_code_spec` (`goods_code`,`spec1_code`,`spec2_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品条码';
