
DROP TABLE IF EXISTS `sys_user_fave`;
CREATE TABLE `sys_user_fave` (
  `user_fave_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  `url` varchar(128) DEFAULT '' COMMENT 'url也可以是操作',
  `name` varchar(128) DEFAULT '' COMMENT '名称',
  `order` int(8) DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`user_fave_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户收藏菜单';


