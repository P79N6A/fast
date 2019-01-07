DROP TABLE IF EXISTS `oms_sell_settlement_detail`;
CREATE TABLE `oms_sell_settlement_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_attr` tinyint(4) NOT NULL DEFAULT '0' COMMENT '单据性质：1销售 2退货',
  `deal_code` varchar(64) NOT NULL DEFAULT '' COMMENT '平台交易号',
  `sale_channel_code` varchar(20) NOT NULL COMMENT '平台代码',
  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号)',
  `goods_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(20) NOT NULL DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(20) NOT NULL DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(30) NOT NULL DEFAULT '' COMMENT 'sku',
  `barcode` varchar(30) NOT NULL DEFAULT '' COMMENT '条码',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
  `avg_money` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '均摊金额',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxu_code` (`sell_record_code`,`deal_code`,`order_attr`,`sku`) USING BTREE,
  KEY `lastchanged` (`lastchanged`) USING BTREE,
  KEY `index1` (`deal_code`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='零售结算明细表';
