DROP TABLE IF EXISTS `op_gift_strategy_log`;
CREATE TABLE `op_gift_strategy_log` (
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `strategy_code` varchar(128) NOT NULL DEFAULT '' COMMENT '策略代码',
  `user_id` varchar(64) DEFAULT '' COMMENT '用户ID',
  `user_code` varchar(64) DEFAULT '' COMMENT '用户代码',
  `action_name` varchar(64) DEFAULT '' COMMENT '操作内容',
  `add_time` datetime DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`log_id`),
  KEY `_key` (`strategy_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='赠品策略操作日志';
