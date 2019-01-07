
DROP TABLE IF EXISTS `api_bs3000j_years`;
CREATE TABLE `api_bs3000j_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
  `DLDM` varchar(50) NOT NULL COMMENT '年份代码',
  `DLMC` varchar(100) DEFAULT NULL COMMENT '年份名称',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `erp_config_id` (`erp_config_id`),
  UNIQUE KEY `DLDM` (`DLDM`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='年份档案表';
