DROP TABLE IF EXISTS `oms_return_package_detail`;
CREATE TABLE `oms_return_package_detail` (
  `return_package_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `return_package_code` varchar(20) NOT NULL DEFAULT '' COMMENT '退货包裹单编号',
  `goods_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(20) NOT NULL DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(20) NOT NULL DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(128) NOT NULL DEFAULT '' COMMENT 'sku',
  `barcode` varchar(128) NOT NULL DEFAULT '' COMMENT '条码',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`return_package_detail_id`),
  UNIQUE KEY `idxu_key` (`return_package_code`,`sku`) USING BTREE,
  KEY `lastchanged` (`lastchanged`) USING BTREE,
  KEY `sku` (`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='销售退货包裹单明细表';