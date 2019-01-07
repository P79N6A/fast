DROP TABLE IF EXISTS `osp_auth_user`;
CREATE TABLE `osp_auth_user` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(128) DEFAULT '' COMMENT '用户名称',
  `phone` varchar(64) DEFAULT '' COMMENT '手机号码',
  `email` varchar(64) DEFAULT '' COMMENT 'email',
  `department` varchar(30) DEFAULT '' COMMENT '部门',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `lastchanged` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;