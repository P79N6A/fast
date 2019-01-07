DROP TABLE IF EXISTS `wms_config`;
CREATE TABLE `wms_config` (
  `wms_config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wms_config_name` varchar(128) NOT NULL DEFAULT '' COMMENT 'wms配置名称',
  `wms_system_code` varchar(64) NOT NULL DEFAULT '' COMMENT '对接wms系统',
  `wms_system` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '对接wms系统',
  `wms_address` varchar(128) NOT NULL DEFAULT '' COMMENT 'wms地址',
  `wms_params` text COMMENT 'wms密钥',
  `item_sync` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'WMS商品库存同步至eFAST: 0 同步, 1 不同步',
  PRIMARY KEY (`wms_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='wms配置';