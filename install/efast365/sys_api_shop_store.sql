DROP TABLE IF EXISTS `sys_api_shop_store`;
CREATE TABLE `sys_api_shop_store` (
  `shop_store_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `p_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联对接ID',
  `p_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '类型: 0 ERP, 1 WMS',
  `shop_store_code` varchar(128) NOT NULL DEFAULT '' COMMENT '仓库商店代码',
  `shop_store_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类型: 0 店铺, 1 仓库',
  `outside_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类型: 0店铺，1仓库',
  `outside_code` varchar(128) NOT NULL DEFAULT '0' COMMENT '对接外部仓库code',
  `store_type` tinyint(3) DEFAULT '1' COMMENT '0为次品仓，1为正品仓 韵达WMS',
  `update_stock` tinyint(3) DEFAULT '1' COMMENT '库存更新0：不更新1；更新 erp对接',
  PRIMARY KEY (`shop_store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='对接仓库店铺关系';