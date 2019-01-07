
DROP TABLE IF EXISTS `base_property_set`;
CREATE TABLE `base_property_set` (
  `property_set_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `property_code` varchar(100) DEFAULT '' COMMENT '属性代码',
  `property_type` varchar(64) DEFAULT '' COMMENT '类型:gooods,',
  `property_val` varchar(64) DEFAULT '' COMMENT '对应property_val1,property_text1',
  `property_val_title` varchar(100) DEFAULT '' COMMENT '属性标题',
  `property_val_type` varchar(100) DEFAULT '' COMMENT '属性类型',
  `is_condition` tinyint(4) DEFAULT '0' COMMENT '是否通用所有0是，1否有条件限制',
  `property_desc` text COMMENT '描述',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`property_set_id`),
  KEY `_key` (`property_code`,`property_type`,`property_val`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='属性设置表';


