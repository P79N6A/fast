
DROP TABLE IF EXISTS `base_unit`;
CREATE TABLE `base_unit` (
  `unit_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `unit_code` varchar(128) DEFAULT '' COMMENT '代码',
  `unit_name` varchar(128) DEFAULT '' COMMENT '名称',
  `relation_code` varchar(128) DEFAULT '0' COMMENT '关联ID',
  `is_fetch` int(4) DEFAULT '0' COMMENT '0：正常 1：待处理',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  PRIMARY KEY (`unit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='单位表';
