DROP TABLE IF EXISTS `oms_return_package_seq`;
CREATE TABLE `oms_return_package_seq` (
  `seq` bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`seq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='包裹单单据自增序列表';