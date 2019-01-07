DROP TABLE IF EXISTS `erp_config`;
CREATE TABLE `erp_config` (
  `erp_config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `erp_config_name` varchar(128) NOT NULL DEFAULT '' COMMENT 'ERP配置名称',
  `erp_system` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '对接ERP系统: 0 BSERP2, 1 BS3000J',
  `erp_address` varchar(128) NOT NULL DEFAULT '' COMMENT 'ERP地址',
  `erp_key` varchar(128) NOT NULL DEFAULT '' COMMENT 'ERP密钥',
  `upload_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'eFAST单据上传: 0 上传零售单据, 1 上传销售日报',
  `manage_stock` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'ERP管理库存: 0 启用, 1 不启用',
  `item_infos_download` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'ERP商品基本信息下载: 0 启用, 1 不启用',
  `erp_params` text NOT NULL COMMENT 'erp密钥参数',
  `online_time` date NOT NULL COMMENT 'erp上线时间',
  `trade_sync` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '单据同步: 0不 启用, 1 启用',
  PRIMARY KEY (`erp_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ERP配置';