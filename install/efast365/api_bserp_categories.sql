
DROP TABLE IF EXISTS `api_bserp_categories`;
CREATE TABLE `api_bserp_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
  `DLDM` varchar(50) NOT NULL COMMENT '分类代码',
  `DLMC` varchar(100) DEFAULT NULL COMMENT '分类名称',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `erp_config_id` (`erp_config_id`),
  UNIQUE KEY `DLDM` (`DLDM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
