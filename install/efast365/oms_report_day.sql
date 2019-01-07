DROP TABLE IF EXISTS `oms_report_day`;
CREATE TABLE `oms_report_day` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(128) DEFAULT '',
  `report_data` varchar(128) DEFAULT '',
  `record_date` date DEFAULT '0000-00-00',
  `record_time` int(11) DEFAULT '0',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`type`,`record_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='零售每天报表';