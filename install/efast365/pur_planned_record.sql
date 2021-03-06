DROP TABLE IF EXISTS `pur_planned_record`;
CREATE TABLE `pur_planned_record` (
  `planned_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_code` varchar(64) DEFAULT '' COMMENT '单据编号',
  `supplier_code` varchar(128) DEFAULT '' COMMENT '供应商代码',
  `record_time` date DEFAULT '0000-00-00' COMMENT '业务时间',
  `store_code` varchar(128) DEFAULT '' COMMENT '仓库代码',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `init_code` varchar(128) DEFAULT '' COMMENT '原单号',
  `planned_time` datetime DEFAULT NULL COMMENT '计划日期',
  `in_time` datetime DEFAULT NULL COMMENT '入库期限',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `finish_num` int(11) DEFAULT '0' COMMENT '完成数量',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '金额',
  `is_check` int(4) DEFAULT '0' COMMENT '0未审核 1审核',
  `is_execute` int(4) DEFAULT '0' COMMENT '0未执行 1执行',
  `is_stop` int(4) DEFAULT '0' COMMENT '0未终止 1终止',
  `is_finish` int(4) DEFAULT '0' COMMENT '0未完成 1完成',
  `is_add_person` varchar(64) DEFAULT '' COMMENT '添加人',
  `is_add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `is_finish_person` varchar(64) DEFAULT '' COMMENT '完成人',
  `is_finish_time` datetime DEFAULT NULL COMMENT '完成时间',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `pur_type_code` varchar(100) DEFAULT '' COMMENT '采购类型代码',
  PRIMARY KEY (`planned_record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='采购计划单';
