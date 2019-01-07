
DROP TABLE IF EXISTS `base_property_val`;
CREATE TABLE `base_property_val` (
  `property_val_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `property_code` varchar(100) DEFAULT NULL COMMENT '属性代码',
  `property_type` varchar(100) DEFAULT NULL,
  `property_name` varchar(100) DEFAULT '' COMMENT '属性名',
  `property_val` varchar(100) DEFAULT '' COMMENT '属性类型',
  `property_desc` text COMMENT '描述',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`property_val_id`),
  KEY `_key` (`property_code`,`property_type`,`property_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='属性对应值';
