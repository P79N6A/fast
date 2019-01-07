DROP TABLE IF EXISTS `sys_sms_tpl`;
CREATE TABLE `sys_sms_tpl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tpl_type` varchar(20) NOT NULL DEFAULT '' COMMENT '短信模版类型',
  `tpl_name` varchar(20) NOT NULL COMMENT '短信模版名称',
  `sms_info` text NOT NULL COMMENT '短信内容',
  `is_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否启用',
  `remark` varchar(255) NOT NULL DEFAULT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='短信模板';

