
DROP TABLE IF EXISTS `goods_property`;
CREATE TABLE `goods_property` (
  `property_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `property_code` varchar(32) DEFAULT NULL COMMENT '属性代码',
  `goods_id` int(11) DEFAULT '0' COMMENT '商品id',
  `goods_code` varchar(64) DEFAULT NULL COMMENT '商品代码',
  `property_set_id` int(11) DEFAULT '0' COMMENT '属性id',
  `property_type` varchar(4) DEFAULT '' COMMENT '属性类型',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  PRIMARY KEY (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品属性';

