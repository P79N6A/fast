
DROP TABLE IF EXISTS `oms_amount_detail`;
CREATE TABLE `oms_amount_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号',
  `deal_code` varchar(30) NOT NULL DEFAULT '' COMMENT '平台交易号',
  `sub_deal_code` varchar(30) NOT NULL DEFAULT '' COMMENT '平台子交易号',
  `goods_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(20) NOT NULL DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(20) NOT NULL DEFAULT '' COMMENT '尺码代码',
  `sku_id` varchar(20) NOT NULL DEFAULT '' COMMENT 'sku_id',
  `sku` varchar(30) NOT NULL DEFAULT '' COMMENT 'sku',
  `barcode` varchar(30) NOT NULL DEFAULT '' COMMENT '条码',
  `goods_price` decimal(10,3) NOT NULL DEFAULT '0.000'  COMMENT '商品单价(实际售价)',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
  `avg_money` decimal(10,3) NOT NULL DEFAULT '0.000'  COMMENT '均摊金额',
  `is_gift` int(4) NOT NULL DEFAULT '0' COMMENT '礼品标识：0-普通商品 1-礼品',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxu_key` (`sell_record_code`,`deal_code`,`sku`) USING BTREE,
  KEY `lastchanged` (`lastchanged`) USING BTREE,
  KEY `sub_deal_code` (`sub_deal_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='零售结算商品明细';