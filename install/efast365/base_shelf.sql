
DROP TABLE IF EXISTS `base_shelf`;
CREATE TABLE `base_shelf` (
  `shelf_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shelf_code` varchar(128) DEFAULT '' COMMENT '库位代码',
  `shelf_name` varchar(128) DEFAULT '' COMMENT '库位名称',
  `shelf_type_code` varchar(128) DEFAULT '' COMMENT '库位类型代码',
  `unit_code` varchar(128) DEFAULT '' COMMENT '单位代码',
  `shelf_status` tinyint(1) DEFAULT '0' COMMENT '货架状态',
  `store_code` varchar(128) DEFAULT '' COMMENT '仓库代码',
  `status` tinyint(1) DEFAULT '1' COMMENT '启用',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`shelf_id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 COMMENT='库位';


