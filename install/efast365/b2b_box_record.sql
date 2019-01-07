
DROP TABLE IF EXISTS `b2b_box_record`;
CREATE TABLE `b2b_box_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_code` varchar(20) DEFAULT '' COMMENT '装箱单编号',
  `task_code` varchar(20) DEFAULT '' COMMENT '装箱任务编号',
  `store_code` varchar(20) DEFAULT '' COMMENT '仓库编号',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `money` decimal(20,2) DEFAULT '0.00' COMMENT '金额',
  `express_code` varchar(20) NOT NULL DEFAULT '' COMMENT '配送方式CODE',
  `express_no` varchar(40) NOT NULL DEFAULT '' COMMENT '快递单号',
  `scan_user` varchar(20) NOT NULL DEFAULT '' COMMENT '扫描人',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `is_check_and_accept` tinyint(4) NOT NULL DEFAULT '0',
  `is_print` tinyint(3) DEFAULT '0' COMMENT '是否打印装箱单',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxu1` (`record_code`) USING BTREE,
  KEY `idx_lastchanged` (`lastchanged`) USING BTREE,
  KEY `idx_express_code` (`express_code`) USING BTREE,
  KEY `idx_express_no` (`express_no`) USING BTREE,
  KEY `idx_store_code` (`store_code`) USING BTREE,
  KEY `idx_task_code` (`task_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='装箱单';