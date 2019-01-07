
DROP TABLE IF EXISTS `stm_profit_loss_lof`;
CREATE TABLE `stm_profit_loss_lof` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_code` varchar(256) DEFAULT '' COMMENT '单据编号',
  `record_code_list` varchar(256) DEFAULT NULL,
  `take_stock_record_code` varchar(128) DEFAULT '' COMMENT '盘点单号',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品编码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '规格编码',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '规格2编码',
  `sku` varchar(128) DEFAULT '' COMMENT '商品编码',
  `store_code` varchar(128) NOT NULL DEFAULT '' COMMENT '仓库代码',
  `lof_no` varchar(64) DEFAULT '' COMMENT '批次号',
  `production_date` date DEFAULT NULL COMMENT '生产日期',
  `num` int(11) DEFAULT '0' COMMENT '账面数量',
  `diff_num` int(11) DEFAULT '0' COMMENT '盈亏数量',
  `status` tinyint(3) DEFAULT '0',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_index_key` (`take_stock_record_code`,`store_code`,`sku`,`lof_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='盈亏批次表';

