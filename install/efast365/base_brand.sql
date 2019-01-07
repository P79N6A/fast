
DROP TABLE IF EXISTS `base_brand`;
CREATE TABLE `base_brand` (
  `brand_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `brand_code` varchar(128) DEFAULT '' COMMENT '品牌代码',
  `brand_name` varchar(128) DEFAULT '' COMMENT '名称',
  `brand_logo` varchar(255) DEFAULT '' COMMENT 'logo',
  `is_fetch` int(4) DEFAULT '0' COMMENT '0：正常 1：待处理',
  `relation_code` varchar(128) DEFAULT '0' COMMENT '关联ID',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  PRIMARY KEY (`brand_id`),
  UNIQUE KEY `brand_code` (`brand_code`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='品牌表';

