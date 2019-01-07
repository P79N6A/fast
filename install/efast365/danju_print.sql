
DROP TABLE IF EXISTS `danju_print`;
CREATE TABLE `danju_print` (
  `print_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT ' 递增ID',
  `print_data_type` varchar(100) DEFAULT NULL COMMENT '某种数据打印类型',
  `danju_print_code` varchar(100) DEFAULT NULL COMMENT ' 单据打印模板编号',
  `danju_print_name` varchar(100) DEFAULT NULL COMMENT ' 单据类型',
  `danju_print_content` longtext COMMENT ' 单据打印模板',
  `customer_print_conf` text,
  `template_page_width` varchar(100) DEFAULT NULL COMMENT '纸张宽度',
  `template_page_height` varchar(100) DEFAULT NULL COMMENT '纸张高度',
  `template_page_style` varchar(20) DEFAULT NULL COMMENT '纸张类型',
  `printer_name` varchar(100) DEFAULT NULL COMMENT '打印机名称',
  `is_default` tinyint(1) DEFAULT '0' COMMENT '是否默认',
  `is_record_ext_print` tinyint(1) DEFAULT '0' COMMENT '是否单据扩展打印',
  `extend_attr` text COMMENT '扩展预留字段',
  `have_shop_setting` int(1) DEFAULT '0' COMMENT '是否有商店独立打印',
  `print_html` longtext COMMENT '打印内容(相对干净html)',
  `print_html_style` longtext COMMENT '打印内容样式',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `is_enable` int(1) DEFAULT '1' COMMENT '是否启用',
  `danju_print_content_pici` LONGTEXT COMMENT ' 带批次单据打印模板',
  `print_html_pici` LONGTEXT COMMENT '打印内容(相对干净html)带批次',
  PRIMARY KEY (`print_id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8 COMMENT='单据打印';
