
DROP TABLE IF EXISTS `sys_db_backup`;
CREATE TABLE `sys_db_backup` (
  `backup_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_path` varchar(255) DEFAULT '' COMMENT '文件路径',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`backup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据库备份';


