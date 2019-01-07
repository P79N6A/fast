DROP TABLE IF EXISTS `sys_login_log`;
CREATE TABLE `sys_login_log` (
  `login_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(4) DEFAULT '0' COMMENT '0：登陆 1：退出',
  `user_id` varchar(64) DEFAULT '' COMMENT '用户ID',
  `user_code` varchar(64) DEFAULT '' COMMENT '用户代码',
  `ip` varchar(128) DEFAULT '' COMMENT 'ip',
  `add_time` datetime DEFAULT NULL COMMENT '登录时间',
  `url` varchar(255) DEFAULT '' COMMENT '当前url',
  `pre_url` varchar(255) DEFAULT '' COMMENT '来源url',
  `browser` varchar(128) DEFAULT '' COMMENT '浏览器',
  PRIMARY KEY (`login_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统登录日志';
