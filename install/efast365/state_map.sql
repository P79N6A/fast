DROP TABLE IF EXISTS `state_map`;
CREATE TABLE `state_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `link_state` varchar(50) NOT NULL DEFAULT '' COMMENT '全链路状态',
  `sys_state` varchar(50) NOT NULL COMMENT '系统状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='淘宝订单全链路状态映射';