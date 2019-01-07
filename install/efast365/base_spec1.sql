
DROP TABLE IF EXISTS `base_spec1`;
CREATE TABLE `base_spec1` (
  `spec1_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `spec1_code` varchar(128) DEFAULT '' COMMENT '代码',
  `spec1_name` varchar(128) DEFAULT '' COMMENT '名称',
  `color_short_name` varchar(128) DEFAULT '' COMMENT '简称',
  `color_aliases_name` varchar(128) DEFAULT '' COMMENT '别名',
  `color_outer_code` varchar(64) DEFAULT '' COMMENT '外部编码',
  `barcode` varchar(64) DEFAULT '' COMMENT '条码对照码',
  `rgb` varchar(64) DEFAULT '' COMMENT 'rgb值',
  `relation_code` varchar(128) DEFAULT '0' COMMENT '关联ID',
  `is_fetch` int(4) DEFAULT '0' COMMENT '0：正常 1：待处理',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  PRIMARY KEY (`spec1_id`),
  UNIQUE KEY `color_code` (`spec1_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='规格1表';

