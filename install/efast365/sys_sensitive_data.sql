DROP TABLE IF EXISTS `sys_sensitive_data`;
CREATE TABLE `sys_sensitive_data` (
  `sys_sensitive_data_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sensitive_name` VARCHAR(64) DEFAULT '' COMMENT '敏感字段名称',
  `sensitive_code` VARCHAR(64) DEFAULT '' COMMENT '敏感字段',
  `type` int(6) DEFAULT '0' COMMENT '类型',
  `relation_table` TEXT COMMENT '数据关系表',
  `start_len` int(6)  DEFAULT '0' COMMENT '前几位显示',
  `end_len` int(6)  DEFAULT '0' COMMENT '后几位显示',
  `desc` VARCHAR(128)  DEFAULT '' COMMENT '说明',
  `example` VARCHAR(128)  DEFAULT '' COMMENT '事例',
  PRIMARY KEY (`sys_sensitive_data_id`),
  UNIQUE KEY `sensitive_code` (`sensitive_code`) USING BTREE
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='敏感数据表';
