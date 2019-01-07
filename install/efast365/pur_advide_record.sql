DROP TABLE IF EXISTS `pur_advide_record`;
CREATE TABLE `pur_advide_record` (
  `record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_date` date DEFAULT '0000-00-00' COMMENT '业务时间',
  `start_time` int(11) DEFAULT '0' COMMENT '开始时间',
  `end_time` int(11) DEFAULT '0' COMMENT '结束时间',
  `is_create_pur` tinyint(3) DEFAULT '0' COMMENT '是否生存采购单',
  `pur_code` text COMMENT '是否生存采购单',
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `_key` (`record_date`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='补货建议记录';
