DROP TABLE IF EXISTS `stm_take_stock_record_detail`;
CREATE TABLE `stm_take_stock_record_detail` (
  `take_stock_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
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
  `num` int(10) DEFAULT '0' COMMENT '实盘数',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '实盘金额',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `record_code` varchar(128) DEFAULT NULL COMMENT '单据编号',
  PRIMARY KEY (`take_stock_record_detail_id`),
  UNIQUE KEY `record_sku` (`record_code`,`sku`) USING BTREE,
  KEY `_index1` (`goods_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='盘点单明细表';
