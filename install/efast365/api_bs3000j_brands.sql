
DROP TABLE IF EXISTS `api_bs3000j_brands`;
CREATE TABLE `api_bs3000j_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
  `PPDM` varchar(50) NOT NULL COMMENT '品牌代码',
  `PPMC` varchar(100) DEFAULT NULL COMMENT '品牌名称',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `erp_config_id` (`erp_config_id`),
  UNIQUE KEY `PPDM` (`PPDM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
