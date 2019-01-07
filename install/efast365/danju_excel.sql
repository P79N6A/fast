
DROP TABLE IF EXISTS `danju_excel`;
CREATE TABLE `danju_excel` (
  `excel_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT ' 递增ID',
  `data_type` varchar(100) DEFAULT NULL COMMENT '单据类型',
  `danju_code` varchar(100) DEFAULT NULL COMMENT '单据导出模板编号',
  `danju_name` varchar(100) DEFAULT NULL COMMENT '单据名称',
  `danju_path` text COMMENT 'Excel模板路径',
  `customer_conf` text COMMENT '自定义配置',
  `is_default` tinyint(1) DEFAULT '0' COMMENT '是否默认',
  `is_record_ext_print` tinyint(1) DEFAULT '0' COMMENT '是否单据扩展',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`excel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8 COMMENT='单据导出';
