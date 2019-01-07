
DROP TABLE IF EXISTS `pur_advide_detail`;
CREATE TABLE `pur_advide_detail` (
  `detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `goods_code` varchar(128) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(128) DEFAULT NULL,
  `spec2_code` varchar(128) DEFAULT NULL,
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `barcode` varchar(128) DEFAULT '' COMMENT '条码',
  `record_date` date DEFAULT '0000-00-00' COMMENT '业务时间',
  `sale_week_num` float(11,2) DEFAULT '0.00' COMMENT '7天销售数量',
  `sale_week_num_all` int(11) DEFAULT NULL,
  `sale_month_num` float(11,2) DEFAULT '0.00' COMMENT '30天销售数量',
  `sale_month_num_all` int(11) DEFAULT NULL,
  PRIMARY KEY (`detail_id`),
  UNIQUE KEY `record_sku` (`record_date`,`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='补货建议明细';
