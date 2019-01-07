DROP TABLE IF EXISTS `sys_role_manage_price`;
CREATE TABLE `sys_role_manage_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_code` varchar(64) DEFAULT '' COMMENT '角色代码',
  `manage_code` varchar(64) DEFAULT '' COMMENT '价格管控类型',
  `status` tinyint(3) DEFAULT '0' COMMENT '是否启用',
  `desc` varchar(255) DEFAULT '' COMMENT '价格管控类型',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_sensitive` (`role_code`,`manage_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='角色价格管控';
