DROP TABLE IF EXISTS `base_suspend_label`;
CREATE TABLE `base_suspend_label` (
  `suspend_label_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `suspend_label_code` varchar(128) NOT NULL DEFAULT '' COMMENT '标签代码',
  `suspend_label_name` varchar(128) NOT NULL DEFAULT '' COMMENT '标签名称',
  `is_sys` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统内置',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `cancel_suspend_time` int NOT NULL DEFAULT '0',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`suspend_label_id`),
  UNIQUE KEY `idxu_suspend_label_code` (`suspend_label_code`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
