
DROP TABLE IF EXISTS `pur_stm_log`;
CREATE TABLE `pur_stm_log` (
  `pur_stm_log_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(64) DEFAULT '' COMMENT '用户ID',
  `user_code` VARCHAR(64) DEFAULT '' COMMENT '用户代码',
  `ip` VARCHAR(128) DEFAULT '' COMMENT 'ip',
  `add_time` DATETIME DEFAULT NULL COMMENT '登录时间',
  `url` VARCHAR(255) DEFAULT '' COMMENT '当前url',
  `pre_url` VARCHAR(255) DEFAULT '' COMMENT '来源url',
  `sure_status` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '确认状态',
  `finish_status` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '完成状态',
  `module` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '模块',
  `action_name` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '操作名称',
  `action_note` MEDIUMTEXT NOT NULL COMMENT '操作描述',
  `pid` INT(11) DEFAULT '0',
  PRIMARY KEY (`pur_stm_log_id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8 COMMENT='进销存操作日志';
