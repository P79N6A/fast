
DROP TABLE IF EXISTS `api_bserp_colors`;
CREATE TABLE `api_bserp_colors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
  `GGDM` varchar(50) NOT NULL COMMENT '颜色代码',
  `GGMC` varchar(100) DEFAULT NULL COMMENT '规格描述',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `erp_config_id` (`erp_config_id`),
  UNIQUE KEY `GGDM` (`GGDM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
