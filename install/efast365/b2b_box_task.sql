
DROP TABLE IF EXISTS `b2b_box_task`;
CREATE TABLE `b2b_box_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_code` varchar(20) DEFAULT '' COMMENT '装箱任务编号',
  `record_type` varchar(20) DEFAULT '' COMMENT '单据类型：wbm_store_out批发出库，pur_purchaser采购入库',
  `relation_code` varchar(20) DEFAULT '' COMMENT '关联单据编号',
  `store_code` varchar(20) DEFAULT '' COMMENT '仓库编号',
  `record_time` datetime DEFAULT NULL COMMENT '业务时间',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `is_check_and_accept` int(4) DEFAULT '0' COMMENT '0未验收 1已验收',
  `is_change` int(4) DEFAULT '0' COMMENT '0未转换 1已转换',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxu1` (`task_code`) USING BTREE,
  UNIQUE KEY `idxu2` (`relation_code`,`record_type`) USING BTREE,
  KEY `idx_lastchanged` (`lastchanged`) USING BTREE,
  KEY `idx_store_code` (`store_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='装箱任务单';