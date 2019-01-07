
DROP TABLE IF EXISTS `req_conf_app`;
CREATE TABLE `req_conf_app` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `key_group` varchar(40) NOT NULL,
  `key_var` varchar(40) NOT NULL,
  `key_name` varchar(40) NOT NULL,
  `value` varchar(256) DEFAULT NULL,
  `ttl` int(11) NOT NULL DEFAULT '86400',
  `key_index` smallint(6) DEFAULT '0' COMMENT 'key在var中的顺序号',
  `desc` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `group` (`key_group`,`key_name`,`key_var`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='fastapp框架配置表 ';

