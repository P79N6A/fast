
DROP TABLE IF EXISTS `base_spec2`;
CREATE TABLE `base_spec2` (
  `spec2_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `spec2_code` varchar(128) DEFAULT '' COMMENT '代码',
  `spec2_name` varchar(128) DEFAULT '' COMMENT '名称',
  `size_short_name` varchar(128) DEFAULT '' COMMENT '简称',
  `size_aliases_name` varchar(128) DEFAULT '' COMMENT '别名',
  `barcode` varchar(64) DEFAULT '' COMMENT '条码对照码',
  `size_outer_code` varchar(64) DEFAULT '' COMMENT '外部编码',
  `row_position` int(4) DEFAULT '0' COMMENT '行位置',
  `col_position` int(4) DEFAULT '0' COMMENT '列位置',
  `relation_code` varchar(128) DEFAULT '0' COMMENT '关联ID',
  `is_fetch` int(4) DEFAULT '0' COMMENT '0：正常 1：待处理',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  PRIMARY KEY (`spec2_id`),
  UNIQUE KEY `size_code` (`spec2_code`) USING BTREE,
  KEY `col_position` (`col_position`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='规格2表';

