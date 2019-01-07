
DROP TABLE IF EXISTS `goods_property_set`;
CREATE TABLE `goods_property_set` (
  `property_set_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `property_code` varchar(64) DEFAULT '' COMMENT '属性代码',
  `property_name` varchar(64) DEFAULT '' COMMENT '属性名称',
  `property_type` varchar(64) DEFAULT '' COMMENT '属性类型',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  PRIMARY KEY (`property_set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品属性';


