DROP TABLE IF EXISTS `sys_role_sensitive_data`;
CREATE TABLE `sys_role_sensitive_data` (
  `sys_role_sensitive_data_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_code` VARCHAR(64) DEFAULT '' COMMENT '角色代码',
  `sensitive_code` VARCHAR(64) DEFAULT '' COMMENT '敏感字段名称',
  `status`tinyint(3) DEFAULT '0' COMMENT '是否启用',
  PRIMARY KEY (`sys_role_sensitive_data_id`),
  UNIQUE KEY `role_sensitive` (`role_code`,`sensitive_code`) USING BTREE
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='敏感数据角色表';