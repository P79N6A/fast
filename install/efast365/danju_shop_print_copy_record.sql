
DROP TABLE IF EXISTS `danju_shop_print_copy_record`;
CREATE TABLE `danju_shop_print_copy_record` (
  `copy_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT ' 递增ID',
  `shop_code` varchar(128) DEFAULT '' COMMENT '商店代码',
  `print_data_type` varchar(100) DEFAULT NULL COMMENT '某种数据打印类型',
  `danju_print_code` varchar(100) DEFAULT NULL COMMENT ' 单据打印模板编号',
  `danju_print_name` varchar(100) DEFAULT NULL COMMENT ' 单据名称',
  `print_data` longtext COMMENT '数据序列化',
  `remark` text COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`copy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商店单据打印复制数据';
