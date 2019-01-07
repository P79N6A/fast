DROP TABLE IF EXISTS `base_supplier_type`;
CREATE TABLE `base_supplier_type` (
  `supplier_type_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `supplier_type_code` VARCHAR(128) DEFAULT '' COMMENT '供应商类型代码',
  `supplier_type_name` VARCHAR(128) DEFAULT '' COMMENT '供应商类型名称',
  PRIMARY KEY (`supplier_type_id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8 COMMENT='供应商类型';