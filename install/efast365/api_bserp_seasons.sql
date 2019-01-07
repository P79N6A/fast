
DROP TABLE IF EXISTS `api_bserp_seasons`;
CREATE TABLE `api_bserp_seasons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
  `JJDM` varchar(50) NOT NULL COMMENT '季节代码',
  `JJMC` varchar(100) DEFAULT NULL COMMENT '季节名称',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `erp_config_id` (`erp_config_id`),
  UNIQUE KEY `JJDM` (`JJDM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
