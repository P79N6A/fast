
DROP TABLE IF EXISTS `goods_spec1`;
CREATE TABLE `goods_spec1` (
  `goods_spec1_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) DEFAULT '0' COMMENT '商品id',
  `spec1_id` int(11) DEFAULT '0' COMMENT '颜色id',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  PRIMARY KEY (`goods_spec1_id`),
  UNIQUE KEY `goods_code_and_color_code` (`goods_code`,`spec1_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8 COMMENT='商品颜色';
