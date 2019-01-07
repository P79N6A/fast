
DROP TABLE IF EXISTS `sys_task_main`;
CREATE TABLE `sys_task_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(128) DEFAULT '',
  `code` varchar(100) DEFAULT NULL COMMENT '任务编码',
  `task_sn` varchar(100) DEFAULT NULL COMMENT '任务编码',
  `request` varchar(255) DEFAULT '' COMMENT '运行参数',
  `is_over` tinyint(3) DEFAULT '0' COMMENT '1开始，2完成，3暂停，4异常',
  `task_type` tinyint(3) DEFAULT '0' COMMENT '0普通定时任务，1分发进程任务,2shell命令',
  `msg` text COMMENT '异常信息',
  `path` varchar(255) DEFAULT '' COMMENT '执行地址',
  `is_set_over` int(11) DEFAULT '0' COMMENT '进程创建是否结束1未结束',
  `process_num` int(11) DEFAULT '3' COMMENT '子任务数量',
  `error_num` int(11) DEFAULT '2',
  `create_time` int(11) DEFAULT '0',
  `create_over_time` int(11) DEFAULT '0',
  `over_time` int(11) DEFAULT '0',
  `start_time` int(11) DEFAULT '0' COMMENT '任务开始时间',
  `process` int(11) DEFAULT '1' COMMENT '子任务并发进程数',
  `is_auto` tinyint(3) DEFAULT '1' COMMENT '是否自动服务1自动服务，0手动服务',
  `is_sys` tinyint(3) DEFAULT '0' COMMENT '是否系统任务0不是，1是',
  `child_task_id` varchar(200) DEFAULT '',
  `loop_time` int(11) DEFAULT '0' COMMENT '循环时间间隔',
  `check_time` int(11) DEFAULT '0',
  `plan_exec_time` int(11) DEFAULT '0' COMMENT '任务计划执行时间',
  `plan_over_time` int(11) DEFAULT '0' COMMENT '计划结束时间',
  `exec_ip` varchar(128) DEFAULT '',
  `plan_exec_ip` varchar(128) DEFAULT '',
  `log_path` varchar(200) DEFAULT '' COMMENT '日志路径',
  PRIMARY KEY (`id`),
  KEY `_index_check` (`is_over`,`task_type`,`exec_ip`,`check_time`) USING BTREE,
  KEY `_index_wait` (`is_over`,`task_type`,`plan_exec_time`,`plan_exec_ip`),
  KEY `_is_auto` (`is_auto`)
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sys_task_main
-- ----------------------------
INSERT INTO `sys_task_main` VALUES ('1', '', 'start_customer_task', 'eb41f19f42fd5a1150e4b86d8cfec792', '{\'act\':\'start_customer_task\'}', '0', '0', '', 'common/crontab/task.php act=start_customer_task', '0', '0', '1', '0', '0', '0', '0', '1', '1', '1', '', '60', '0', '0', '0', '', '', 'logs/crontab/task_1.log');
INSERT INTO `sys_task_main` VALUES ('2', '', 'clear_task', '8b1345c99ab6f83bbbee46638f99f43e', '{\'act\':\'clear_task\'}', '0', '0', '', 'common/crontab/task.php act=clear_task', '0', '0', '1', '0', '0', '0', '0', '1', '1', '1', '', '86400', '0', '0', '0', '', '', 'logs/crontab/task_2.log');
INSERT INTO `sys_task_main` VALUES ('3', '', 'monitor_task', '7b16f5fa5d4eee101093117fa79573da', '{\'act\':\'monitor_task\'}', '0', '0', '', 'common/crontab/task.php act=monitor_task', '0', '0', '1', '0', '0', '0', '0', '1', '1', '1', '', '900', '0', '0', '0', '', '', 'logs/crontab/task_3.log');
INSERT INTO sysdb.`sys_task_main` VALUES ('4', '', 'tb_log_task', '12b16f5fa5d4ee323293117fa79573da', '{"app_act":"sys_cli\/tb_log","app_fmt":"json"}', '0', '0', '', 'webefast/web/index.php \'app_act=sys_cli/tb_log\'', '0', '0', '1', '0', '0', '0', '0', '1', '1', '1', '', '3600', '0', '0', '0', '', '', 'logs/crontab/task_4.log');
