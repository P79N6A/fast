DROP TABLE IF EXISTS `sys_user_pref`;
CREATE TABLE `sys_user_pref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `iid` varchar(50) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_id_type_iid` (`user_id`,`type`,`iid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户偏好设置';
