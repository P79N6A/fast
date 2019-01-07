
DROP TABLE IF EXISTS `oms_deliver_record_detail`;
CREATE TABLE `oms_deliver_record_detail` (
  `deliver_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `deliver_record_id` int(11) DEFAULT '0' COMMENT '发货单主键',
  `sell_record_code` varchar(64) DEFAULT '' COMMENT '订单编号',
  `deal_code` varchar(64) DEFAULT '' COMMENT '平台交易号',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `barcode` varchar(128) DEFAULT '' COMMENT '条码',
  `platform_spec` varchar(255) NOT NULL DEFAULT '' COMMENT '平台规格',
  `goods_price` decimal(20,3) DEFAULT '0.000' COMMENT '商品单价(实际售价)',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `goods_weigh` decimal(20,3) DEFAULT '0.000' COMMENT '商品重量',
  `weigh_express_money` decimal(20,3) DEFAULT '0.000' COMMENT '商品称重运费',
  `avg_money` decimal(20,3) DEFAULT '0.000' COMMENT '均摊金额',
  `scan_num` int(11) DEFAULT '0' COMMENT '扫描数量（拣货单扫描模式）',
  `is_gift` int(4) DEFAULT '0' COMMENT '礼品标识：0-普通商品1-礼品',
  `is_real_stock_out` int(4) DEFAULT '0' COMMENT '是否实物缺货 0未缺货 1缺货 2部分缺货',
  `is_stock_out_num` int(10) DEFAULT '0' COMMENT '缺货数量',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `waves_record_id` int(11) DEFAULT '0' COMMENT '波次单编号',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`deliver_record_detail_id`),
  UNIQUE KEY `idxu_key` (`sell_record_code`,`deal_code`,`sku`,`is_gift`,`waves_record_id`) USING BTREE,
  KEY `_deliver_record_id` (`deliver_record_id`) USING BTREE,
  KEY `_waves_record_id_` (`waves_record_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='发货订单明细表';

