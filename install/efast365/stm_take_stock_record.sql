DROP TABLE IF EXISTS `stm_take_stock_record`;
CREATE TABLE `stm_take_stock_record` (
  `take_stock_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_code` varchar(200) DEFAULT '' COMMENT '单据编号',
  `relation_code` varchar(200) DEFAULT '' COMMENT '关联单号',
  `status` int(4) DEFAULT '0' COMMENT '0:未验收 1：未盈亏 2：已盈亏',
  `init_code` varchar(128) DEFAULT '' COMMENT '原单号',
  `take_stock_type` int(1) DEFAULT '0' COMMENT '盘点类型1=全部，2=商品级抽盘，3=明细级抽盘，4=指定商品范围盘点，5=指定SKU范围盘点',
  `store_code` varchar(128) DEFAULT '' COMMENT '盘点仓库代码',
  `org_code` varchar(128) DEFAULT '000' COMMENT '渠道代码',
  `user_code` varchar(64) DEFAULT '' COMMENT '业务员代码',
  `record_time` date DEFAULT '0000-00-00' COMMENT '业务时间',
  `take_stock_time` date DEFAULT '0000-00-00' COMMENT '盘点时间',
  `price_type` varchar(64) DEFAULT '' COMMENT '价格类型',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `num` int(10) DEFAULT '0' COMMENT '实盘数',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '实盘金额',
  `repeat_take_stock_num` int(2) DEFAULT '0' COMMENT '重盘次数',
  `is_add_person` varchar(64) DEFAULT '' COMMENT '添加人',
  `is_add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `is_sure` int(4) DEFAULT '0' COMMENT '0未确认 1确认',
  `is_sure_person` varchar(64) DEFAULT '' COMMENT '确认人',
  `is_sure_time` datetime DEFAULT NULL COMMENT '确认时间',
  `is_pre_profit_and_loss` int(4) DEFAULT '0' COMMENT '0未预盈亏 1已预盈亏',
  `is_pre_profit_and_loss_person` varchar(64) DEFAULT '' COMMENT '预盈亏人',
  `is_pre_profit_and_loss_time` datetime DEFAULT NULL COMMENT '预盈亏时间',
  `is_stop` int(4) DEFAULT '0' COMMENT '0未终止 1已终止(由上游单据终止)',
  `is_stop_person` varchar(64) DEFAULT '' COMMENT '终止人',
  `is_stop_time` datetime DEFAULT NULL COMMENT '终止时间',
  `take_stock_task_id` int(11) DEFAULT '0',
  `take_stock_status` tinyint(4) DEFAULT '0' COMMENT '1库存维护，2计算账面数，3生成调整单，4调整库存，5完成，9异常',
  `message` varchar(255) DEFAULT '' COMMENT '异常信息',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`take_stock_record_id`),
  UNIQUE KEY `record_code` (`record_code`) USING BTREE,
  KEY `store_code` (`store_code`) USING BTREE,
  KEY `user_code` (`user_code`) USING BTREE,
  KEY `record_time` (`record_time`) USING BTREE,
  KEY `num` (`num`) USING BTREE,
  KEY `money` (`money`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='盘点单';
