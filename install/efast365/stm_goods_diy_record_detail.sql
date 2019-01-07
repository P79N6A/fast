CREATE TABLE `stm_goods_diy_record_detail` (
  `goods_diy_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `price` decimal(20,3) DEFAULT '0.000' COMMENT '单价',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '金额',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `record_code` varchar(128) DEFAULT NULL COMMENT '单据编号',
  `lof_no` varchar(64) DEFAULT '' COMMENT '批次号',
  `production_date` date DEFAULT NULL COMMENT '生产日期',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `type` varchar(100) DEFAULT 'diy' COMMENT 'diy:组装；lof:批次',
  `diy_sku` varchar(100) DEFAULT '' COMMENT '组装商品sku',
  PRIMARY KEY (`goods_diy_record_detail_id`),
  UNIQUE KEY `record_sku` (`record_code`,`diy_sku`,`sku`,`lof_no`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品组装单明细表';