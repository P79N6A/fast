DROP TABLE IF EXISTS `op_strategy_log`;
CREATE TABLE `op_strategy_log` (
  `op_strategy_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(128) DEFAULT '0' COMMENT 'gift:赠品策略',
  `strategy_code` varchar(128) DEFAULT '' COMMENT '策略code',
  `strategy_detail_id` varchar(255) DEFAULT '' COMMENT '策略明细ID',
  `deal_code` varchar(128) DEFAULT '' COMMENT '交易号',
  `sell_record_code` varchar(128) DEFAULT '' COMMENT '订单号',
  `customer_code` varchar(128) DEFAULT '0' COMMENT '用户ID',
  `is_success` tinyint(3) DEFAULT '1' COMMENT '1成功，0失败',
  `strategy_content` text COMMENT '策略内容',
  `desc` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`op_strategy_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='策略日志';

