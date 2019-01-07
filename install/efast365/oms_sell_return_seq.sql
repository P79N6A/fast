DROP TABLE IF EXISTS `oms_sell_return_seq`;
CREATE TABLE `oms_sell_return_seq` (
  `seq` bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`seq`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8 COMMENT='单据自增序列表';