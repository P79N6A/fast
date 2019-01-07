DROP TABLE IF EXISTS `fx_purchaser_record_detail`;
CREATE TABLE `fx_purchaser_record_detail` (
  `purchaser_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `record_code` varchar(128) DEFAULT NULL COMMENT '单据编号',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `price` decimal(20,3) DEFAULT '0.000' COMMENT '采购单价',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '金额',
  `finish_num` int(11) DEFAULT '0' COMMENT '实际出库数',
  `num` int(11) DEFAULT '0' COMMENT '计划采购数',
  `goods_property` int(4) DEFAULT '0' COMMENT '商品性质 0-正常 1-回写',
  `cost_price` decimal(20,3) DEFAULT '0.000' COMMENT '成本单',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`purchaser_record_detail_id`),
  UNIQUE KEY `record_sku` (`record_code`,`sku`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品采购入库单明细表';
