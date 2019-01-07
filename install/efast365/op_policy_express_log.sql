DROP TABLE IF EXISTS `op_policy_express_log`;
CREATE TABLE IF NOT EXISTS `op_policy_express_log` (
	`log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`pid` varchar(128) NOT NULL DEFAULT '' COMMENT '策略id',
	`user_id` varchar(64) DEFAULT '' COMMENT '用户ID',
	`user_code` varchar(64) DEFAULT '' COMMENT '用户代码',
	`action_name` varchar(64) DEFAULT '' COMMENT '操作内容',
	`add_time` datetime DEFAULT NULL COMMENT '操作时间',
	PRIMARY KEY (`log_id`),
	KEY `_key` (`pid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='赠品策略操作日志';

