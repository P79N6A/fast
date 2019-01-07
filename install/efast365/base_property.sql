
DROP TABLE IF EXISTS `base_property`;
CREATE TABLE `base_property` (
  `property_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `property_type` varchar(64) DEFAULT '' COMMENT '类型:gooods,',
  `property_val_code` varchar(100) DEFAULT '' COMMENT '对应值CODE',
  `property_val1` varchar(100) DEFAULT '' COMMENT '属性1',
  `property_val2` varchar(100) DEFAULT '' COMMENT '属性2',
  `property_val3` varchar(100) DEFAULT '' COMMENT '属性3',
  `property_val4` varchar(100) DEFAULT '' COMMENT '属性4',
  `property_val5` varchar(100) DEFAULT '' COMMENT '属性5',
  `property_val6` varchar(100) DEFAULT '' COMMENT '属性6',
  `property_val7` varchar(100) DEFAULT '' COMMENT '属性7',
  `property_val8` varchar(100) DEFAULT '' COMMENT '属性8',
  `property_val9` varchar(100) DEFAULT '' COMMENT '属性9',
  `property_val10` varchar(100) DEFAULT '' COMMENT '属性10',
  `property_val11` varchar(100) DEFAULT '0' COMMENT '属性11',
  `property_val12` varchar(100) DEFAULT '0' COMMENT '属性12',
  `property_val13` varchar(100) DEFAULT '0' COMMENT '属性13',
  `property_val14` varchar(100) DEFAULT '0' COMMENT '属性14',
  `property_val15` varchar(100) DEFAULT '0' COMMENT '属性15',
  `property_val16` varchar(100) DEFAULT '0' COMMENT '属性16',
  `property_val17` varchar(100) DEFAULT '0' COMMENT '属性17',
  `property_val18` varchar(100) DEFAULT '0' COMMENT '属性18',
  `property_val19` varchar(100) DEFAULT '0' COMMENT '属性19',
  `property_val20` varchar(100) DEFAULT '0' COMMENT '属性20',
  `property_text1` varchar(200) DEFAULT '' COMMENT '属性文本类型1',
  `property_text2` varchar(200) DEFAULT '' COMMENT '属性文本类型2',
  `property_text3` varchar(200) DEFAULT '' COMMENT '属性文本类型3',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`property_id`),
  UNIQUE KEY `_key` (`property_type`,`property_val_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='扩展属性表';

