
DROP TABLE IF EXISTS `sys_action`;
CREATE TABLE `sys_action` (
  `action_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  `type` varchar(10) NOT NULL DEFAULT '' COMMENT 'cote大模块, group菜单组, url菜单',
  `action_name` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单名',
  `action_code` varchar(255) NOT NULL DEFAULT '' COMMENT '菜单地址',
  `sort_order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '0:数字越小越靠前',
  `appid` int(11) DEFAULT NULL COMMENT '预留字段,暂时不用, 多个应用的编号',
  `other_priv_type` int(11) NOT NULL DEFAULT '0' COMMENT '预留字段, 暂时不用',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用 0不启用 1启用',
  `ui_entrance` tinyint(3) NOT NULL DEFAULT '0' COMMENT '系统界面入口，0-默认 1-API',
  PRIMARY KEY (`action_id`),
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=80000001 DEFAULT CHARSET=utf8 COMMENT='操作菜单表';
