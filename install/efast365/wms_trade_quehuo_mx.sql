DROP TABLE IF EXISTS `wms_trade_quehuo_mx`;
CREATE TABLE `wms_trade_quehuo_mx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `efast_store_code` varchar(30) NOT NULL DEFAULT '' COMMENT 'efast仓库代码',
  `wms_store_code` varchar(30) NOT NULL DEFAULT '' COMMENT 'wms仓库代码',
  `sell_record_code` varchar(20) NOT NULL COMMENT 'efast订单号',
  `barcode` varchar(30) NOT NULL COMMENT '条码',
  `qh_num` int(11) NOT NULL DEFAULT '0' COMMENT '缺货数量',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '订单发货数量',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxu` (`sell_record_code`,`barcode`) USING BTREE,
  KEY `sell_record_code` (`sell_record_code`),
  KEY `barcode` (`barcode`),
  KEY `qh_sl` (`qh_num`),
  KEY `sl` (`num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
