DROP TABLE IF EXISTS `pur_return_record_detail`;
CREATE TABLE `pur_return_record_detail` (
  `return_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `goods_id` int(11) DEFAULT '0' COMMENT 'goods_id',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_id` int(11) DEFAULT '0' COMMENT 'color_id',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_id` int(11) DEFAULT '0' COMMENT 'size_id',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `refer_price` decimal(20,3) DEFAULT '0.000' COMMENT '参考价',
  `price` decimal(20,3) DEFAULT '0.000' COMMENT '单价',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '金额',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `goods_property` int(4) DEFAULT '0' COMMENT '商品性质 0-正常 1-回写',
  `cost_price` decimal(20,3) DEFAULT '0.000' COMMENT '成本单',
  `cost_price_forecast` decimal(20,3) DEFAULT '0.000' COMMENT '预估成本单',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `record_code` varchar(128) DEFAULT NULL COMMENT '单据编号',
  `enotice_num` int(11) DEFAULT '0' COMMENT '通知数量',
  PRIMARY KEY (`return_record_detail_id`),
  UNIQUE KEY `record_sku` (`record_code`,`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品采购退货单明细表';