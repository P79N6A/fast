DROP TABLE IF EXISTS `oms_sell_change_detail`;
CREATE TABLE `oms_sell_change_detail` (
  `sell_change_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_return_code` varchar(20) NOT NULL DEFAULT '' COMMENT '退单编号',
  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '订单编号',
  `deal_code` varchar(200) NOT NULL DEFAULT '' COMMENT '平台交易号',
  `goods_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(20) NOT NULL DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(20) NOT NULL DEFAULT '' COMMENT '尺码代码',
  `sku_id` varchar(20) NOT NULL DEFAULT '' COMMENT '平台的sku_id',
  `sku` varchar(128) NOT NULL DEFAULT '' COMMENT 'sku',
  `barcode` varchar(128) NOT NULL DEFAULT '' COMMENT '条码',
  `goods_price` decimal(20,3) NOT NULL DEFAULT '0.00' COMMENT '商品单价',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
  `avg_money` decimal(20,3) NOT NULL DEFAULT '0.00' COMMENT '均摊金额',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`sell_change_detail_id`),
  UNIQUE KEY `idxu_key` (`sell_return_code`,`deal_code`,`sku`) USING BTREE,
  KEY `deal_code` (`deal_code`),
  KEY `barcode` (`barcode`),
  KEY `lastchanged` (`lastchanged`),
  KEY `sku` (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='销售退单换货明细表';