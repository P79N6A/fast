DROP TABLE IF EXISTS `oms_waves_strategy`;
CREATE TABLE `oms_waves_strategy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_code` varchar(128) DEFAULT '0',
  `type` smallint(5) DEFAULT '0' COMMENT '0单SKU，1包含某个SKU',
  `code` varchar(128) DEFAULT '',
  `name` varchar(255) DEFAULT '',
  `condition` text,
  `is_sys` tinyint(3) DEFAULT '0' COMMENT '是否系统策略',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;