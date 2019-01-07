
DROP TABLE IF EXISTS `base_category`;
CREATE TABLE `base_category` (
  `category_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_code` varchar(128) DEFAULT '' COMMENT '代码',
  `p_code` varchar(128) DEFAULT '0' COMMENT '上级代码',
  `category_name` varchar(128) DEFAULT '' COMMENT '名称',
  `relation_code` varchar(128) DEFAULT '0' COMMENT '关联ID',
  `is_fetch` int(4) DEFAULT '0' COMMENT '0：正常 1：待处理',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_code` (`category_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1383 DEFAULT CHARSET=utf8 COMMENT='分类表';
