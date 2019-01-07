DROP TABLE IF EXISTS `shop_store`;
CREATE TABLE `shop_store` (
  `shop_store_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_store` int(10) unsigned NOT NULL COMMENT '对接店铺或仓库',
  `shop_store_code` varchar(128) NOT NULL DEFAULT '' COMMENT '代码',
  `shop_store_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类型: 0 店铺, 1 仓库',
  `outside_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类型: 0 ERP, 1 WMS',
  `outside_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '店铺或仓库id',
  PRIMARY KEY (`shop_store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='对接店铺或仓库';