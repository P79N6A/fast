
DROP TABLE IF EXISTS `sys_role_action`;
CREATE TABLE `sys_role_action` (
  `role_id` int(11) unsigned NOT NULL DEFAULT '0',
  `action_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`action_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色权限关联表';

