
DROP TABLE IF EXISTS `b2b_box_record_detail`;
CREATE TABLE `b2b_box_record_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_code` varchar(20) DEFAULT '' COMMENT '装箱单编号',
  `task_code` varchar(20) DEFAULT '' COMMENT '装箱任务编号',
  `goods_code` varchar(20) DEFAULT '' COMMENT '商品编码',
  `spec1_code` varchar(20) DEFAULT '' COMMENT '规格编码',
  `spec2_code` varchar(20) DEFAULT '' COMMENT '规格2编码',
  `sku` varchar(30) DEFAULT '' COMMENT 'SKU编码',
  `lof_no` varchar(30) DEFAULT '' COMMENT '批次号',
  `production_date` date DEFAULT NULL COMMENT '生产日期',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_idxu` (`record_code`,`sku`,`lof_no`,`production_date`) USING BTREE,
  KEY `lastchanged` (`lastchanged`),
  KEY `_idx_task_code` (`task_code`,`sku`,`lof_no`,`production_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='装箱商品明细';