
DROP TABLE IF EXISTS `sys_schedule`;
CREATE TABLE `sys_schedule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `code` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '任务名称',
  `task_type_code` varchar(50) NOT NULL DEFAULT '' COMMENT '任务CODE类型',
  `sale_channel_code` varchar(100) NOT NULL DEFAULT '' COMMENT '类型名称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0-关闭，1-开启）',
  `type` tinyint(2) DEFAULT '0' COMMENT '0系统，1接口，2WMS,3ERP',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '计划任务描述',
  `request` varchar(255) DEFAULT '' COMMENT 'path参数',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '计划任务脚本全路径{webapp/models/crontab/cron.php}',
  `max_num` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '同时运行计划任务的数量',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `last_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '执行时间',
  `loop_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '执行间隔',
  `task_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0普通定时任务，2shell命令,1分发任务（不用）',
  `task_module` varchar(100) DEFAULT 'api' COMMENT '任务所属模块',
  `exec_ip` varchar(120) DEFAULT '' COMMENT '执行IP',
  `plan_exec_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '计划执行时间',
  `plan_exec_data` text,
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`) USING BTREE,
  KEY `update_time` (`update_time`) USING BTREE,
  KEY `status` (`status`,`task_type_code`,`sale_channel_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
