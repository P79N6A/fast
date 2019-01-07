
DROP TABLE IF EXISTS `sys_task_process`;
CREATE TABLE `sys_task_process` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) DEFAULT '0',
  `process_sn` varchar(100) DEFAULT '',
  `create_time` int(11) NOT NULL,
  `request` varchar(500) DEFAULT '',
  `path` text NOT NULL,
  `is_over` tinyint(3) DEFAULT '0' COMMENT '0未开始 1开始 2完成 3异常',
  `start_time` int(11) DEFAULT '0',
  `run_time` int(11) NOT NULL DEFAULT '0' COMMENT '运行次数',
  `task_id` int(11) NOT NULL DEFAULT '0' COMMENT '主任务id',
  `exec_num` tinyint(3) DEFAULT '1',
  `error_num` tinyint(3) DEFAULT '3' COMMENT '允许错误次数',
  `is_get_over` tinyint(3) DEFAULT '1' COMMENT '0进程不回写完成，1进程完成处理，2回写完成',
  `over_time` int(11) DEFAULT '0' COMMENT '结束时间',
  `check_time` int(11) DEFAULT '0',
  `msg` varchar(200) DEFAULT '',
  `is_auto` tinyint(3) DEFAULT '0' COMMENT '是否自动',
  PRIMARY KEY (`id`),
  KEY `index_over_task` (`is_over`,`task_id`) USING BTREE,
  KEY `index_over` (`is_over`,`is_auto`) USING BTREE,
  KEY `index_process_sn` (`is_over`,`process_sn`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8;

