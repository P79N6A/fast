
DROP TABLE IF EXISTS `sys_version`;
CREATE TABLE `sys_version` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `version_num` varchar(50) NOT NULL DEFAULT '' COMMENT '主版本号|补丁版本号',
  `is_main_version` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否主版本',
  `parent_version_num` varchar(50) NOT NULL DEFAULT '' COMMENT '主版本号',
  `public_date` varchar(50) NOT NULL DEFAULT '' COMMENT '发布日期',
  `update_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '升级状态: 0升级初始 1升级中 2升级完成 3升级失败',
  `about_url` varchar(100) NOT NULL DEFAULT '' COMMENT '版本特性url',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `relation_patch_code` varchar(30) NOT NULL DEFAULT '' COMMENT '关联补丁包code',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `version_num` (`version_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='软件版本升级信息表'