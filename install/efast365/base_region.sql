
DROP TABLE IF EXISTS `base_region`;
CREATE TABLE `base_region` (
  `region_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned DEFAULT '0' COMMENT '上级id',
  `region_name` varchar(120) DEFAULT '' COMMENT '地区名称',
  `region_type` tinyint(1) DEFAULT '2' COMMENT '地区类型1：省，2：市，3：区',
  `sys` int(1) DEFAULT '0' COMMENT '是否是系统值 0：不是 1：是 ',
  PRIMARY KEY (`region_id`),
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `region_type` (`region_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=990101 DEFAULT CHARSET=utf8 COMMENT='地区';
