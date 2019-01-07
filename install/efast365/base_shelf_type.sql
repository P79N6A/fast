
DROP TABLE IF EXISTS `base_shelf_type`;
CREATE TABLE `base_shelf_type` (
  `shelf_type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shelf_type_code` varchar(128) DEFAULT '' COMMENT '库位类型代码',
  `shelf_type_name` varchar(128) DEFAULT '' COMMENT '库位类型名称',
  `status` tinyint(1) DEFAULT '1' COMMENT '启用',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`shelf_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='库位类型';
