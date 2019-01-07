DROP TABLE IF EXISTS `platform_log`;
CREATE TABLE `platform_log` (
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `platform_code` varchar(20) NOT NULL DEFAULT '' COMMENT '平台代码',
  `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
  `business_type_id` int(11) NOT NULL DEFAULT '0' COMMENT '业务类型id',
  `action_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '业务操作描述',
  `do_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '执行时间',
  `do_person` varchar(20) NOT NULL COMMENT '执行人',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='平台日志';