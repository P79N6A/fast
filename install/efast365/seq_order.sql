
DROP TABLE IF EXISTS `seq_order`;
CREATE TABLE `seq_order` (
  `seq` bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`seq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='单据自增序列表';
