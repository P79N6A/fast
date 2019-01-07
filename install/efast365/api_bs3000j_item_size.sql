
DROP TABLE IF EXISTS `api_bs3000j_item_size`;
CREATE TABLE `api_bs3000j_item_size` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
  `SPDM` varchar(50) NOT NULL COMMENT '商品代码',
  `GGDM` varchar(50) NOT NULL COMMENT '尺码代码',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `erp_config_id` (`erp_config_id`),
  UNIQUE KEY `SPDMGGDM` (`SPDM`,`GGDM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
