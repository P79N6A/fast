DROP TABLE IF EXISTS `oms_report_day_sku`;
CREATE TABLE `oms_report_day_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(5) DEFAULT '0',
  `num` int(11) DEFAULT '0',
  `sku` varchar(128) DEFAULT '',
  `record_date` date DEFAULT '0000-00-00',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`type`,`sku`,`record_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='零售SKU销售前10';