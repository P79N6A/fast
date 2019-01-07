
DROP TABLE IF EXISTS `sys_operate_log`;
CREATE TABLE `sys_operate_log` (
  `operate_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(64) DEFAULT '' COMMENT '表名',
  `table_id` varchar(64) DEFAULT '' COMMENT '主键id',
  `user_id` varchar(64) DEFAULT '' COMMENT '用户ID',
  `user_code` varchar(64) DEFAULT '' COMMENT '用户代码',
  `ip` varchar(128) DEFAULT '' COMMENT 'ip',
  `add_time` datetime DEFAULT NULL COMMENT '登录时间',
  `url` varchar(255) DEFAULT '' COMMENT '当前url',
  `pre_url` varchar(255) DEFAULT '' COMMENT '来源url',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `module` varchar(100) DEFAULT '' COMMENT '业务模块',
  `yw_code` varchar(100) DEFAULT '' COMMENT '业务code',
  `operate_xq` text COMMENT '操作详情',
  `operate_type` varchar(50) DEFAULT '' COMMENT '操作类型',
  PRIMARY KEY (`operate_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统操作日志';
