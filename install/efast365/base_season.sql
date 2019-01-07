
DROP TABLE IF EXISTS `base_season`;
CREATE TABLE `base_season` (
  `season_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `season_code` varchar(128) DEFAULT '' COMMENT '代码',
  `season_name` varchar(128) DEFAULT '' COMMENT '名称',
  `relation_code` varchar(128) DEFAULT '0' COMMENT '关联ID',
  `is_fetch` int(4) DEFAULT '0' COMMENT '0：正常 1：待处理',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  PRIMARY KEY (`season_id`),
  UNIQUE KEY `season_code` (`season_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='季节表';

