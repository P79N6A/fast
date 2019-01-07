DROP TABLE IF EXISTS `fx_custom_grades_detail`;
CREATE TABLE `fx_custom_grades_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `grade_code` varchar(128) DEFAULT '' COMMENT '等级代码',
  `custom_type` varchar(128) DEFAULT '' COMMENT '分销商类型',
  `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
  `custom_name` varchar(128) DEFAULT '' COMMENT '分销商名称',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分销商等级对应分销商详情表';
