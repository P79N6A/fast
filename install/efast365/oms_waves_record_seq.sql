
DROP TABLE IF EXISTS `oms_waves_record_seq`;
CREATE TABLE `oms_waves_record_seq` (
  `seq` bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`seq`)
) ENGINE=InnoDB  CHARSET=utf8 COMMENT='单据自增序列表';

