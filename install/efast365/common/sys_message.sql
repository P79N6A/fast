
DROP TABLE IF EXISTS `sys_message`;
CREATE TABLE `sys_message` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(128) DEFAULT NULL COMMENT '消息代码:task定时任务，upgrade 升级',
  `data` text,
  `log` text,
  `type` tinyint(4) DEFAULT '0' COMMENT '0普通消息，1警告',
  `create_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

