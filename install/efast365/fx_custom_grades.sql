DROP TABLE IF EXISTS `fx_custom_grades`;
CREATE TABLE `fx_custom_grades` (
  `grade_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `grade_code` varchar(128) DEFAULT '' COMMENT '等级代码',
  `grade_name` varchar(128) DEFAULT '' COMMENT '等级名称',
  `custom_num` int(11) DEFAULT '0' COMMENT '分销数量',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`grade_id`),
  UNIQUE KEY `account_code` (`grade_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分销商等级表';
