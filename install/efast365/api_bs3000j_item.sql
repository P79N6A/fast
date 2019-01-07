
DROP TABLE IF EXISTS `api_bs3000j_item`;
CREATE TABLE `api_bs3000j_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
  `SPDM` varchar(50) NOT NULL COMMENT '商品编号',
  `SPMC` varchar(200) DEFAULT NULL COMMENT '商品名称',
  `BZSJ` decimal(10,2) DEFAULT NULL COMMENT '标准售价',
  `CKJ_IN` decimal(10,2) DEFAULT NULL COMMENT '整调价',
  `BYZD3` varchar(50) DEFAULT NULL COMMENT '品牌',
  `BYZD4` varchar(50) DEFAULT NULL COMMENT '分类',
  `BYZD5` varchar(50) DEFAULT NULL COMMENT '季节',
  `BYZD8` varchar(50) DEFAULT NULL COMMENT '年份',
  `DWMC` varchar(50) DEFAULT NULL COMMENT '单位名称',
  `TZSY` varchar(50) DEFAULT NULL,
  `IDRanK` varchar(50) DEFAULT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `SPDM` (`SPDM`),
  KEY `erp_config_id` (`erp_config_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
