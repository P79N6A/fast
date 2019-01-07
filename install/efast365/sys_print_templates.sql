
DROP TABLE IF EXISTS `sys_print_templates`;
CREATE TABLE `sys_print_templates` (
  `print_templates_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `print_templates_code` varchar(128) DEFAULT NULL,
  `print_templates_name` varchar(128) NOT NULL DEFAULT '' COMMENT '模板名称',
  `company_code` varchar(128) DEFAULT NULL,
  `type` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '类型: 1快递模板, 2发货单, 3拣货单, 4条码打印, 5批发销货单',
  `is_buildin` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否内置: 0不是, 1是',
  `offset_top` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '上偏移量',
  `offset_left` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '左偏移量',
  `paper_width` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '纸张宽度',
  `paper_height` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '纸张高度',
  `printer` varchar(128) NOT NULL DEFAULT '' COMMENT '打印机',
  `template_val` text NOT NULL COMMENT '特殊键值数据处理',
  `template_body` text NOT NULL COMMENT '模板内容',
  `template_body_replace` text NOT NULL,
  `template_body_default` text NOT NULL COMMENT '默认模板内容',
  PRIMARY KEY (`print_templates_id`),
  UNIQUE KEY `print_templates_code` (`print_templates_code`),
) ENGINE=InnoDB AUTO_INCREMENT=920 DEFAULT CHARSET=utf8 COMMENT='打印模板';
