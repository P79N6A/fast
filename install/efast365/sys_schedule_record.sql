DROP TABLE IF EXISTS `sys_schedule_record`;
CREATE TABLE `sys_schedule_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_code` varchar(50) DEFAULT '' COMMENT '类型code',
  `shop_code` varchar(50) DEFAULT '' COMMENT '商店代码',
  `exec_time` int(11) DEFAULT '0',
  `all_loop_time` int(11) DEFAULT '3600',
  `all_exec_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_code` (`type_code`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;