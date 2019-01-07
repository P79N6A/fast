<?php

$u = array();

$u['FSF-1587'] = array(
		"INSERT INTO `sys_action` VALUES ('3020400', '3020000', 'url', '订单合并规则', 'oms/order_combine_strategy/do_list', '5', '1', '0', '1','0');",
		"DROP TABLE IF EXISTS `order_combine_strategy`;",
		"CREATE TABLE `order_combine_strategy` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
		  `rule_code` varchar(40) NOT NULL DEFAULT '' COMMENT '规则代码',
		  `rule_status_value` varchar(40) NOT NULL DEFAULT '0' COMMENT '规则状态值',
		  `rule_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '规则说明',
		  `rule_scene_value` varchar(40) NOT NULL DEFAULT '' COMMENT '规则场景值',
			`remark` varchar(255)  NOT NULL DEFAULT '' COMMENT '说明',
		  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `rule_code` (`rule_code`)
		) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=utf8 COMMENT='订单合并策略';",
		"INSERT  INTO `order_combine_strategy`(rule_code,rule_status_value,rule_desc,rule_scene_value,remark) VALUES ( 'order_outo_combine', '1', '已打印的订单不参与合并（快递单打印或发货单打印，系统均认为为已打印），默认启用', '0', 'rule_status_value:1-开启 0-关闭;rule_scene_value:0-仅自动合并 1-手工合并自动合并');"
);
$u['FSF-1575'] = array(
		"INSERT INTO `sys_action` VALUES ('12030100','12030000','url','BS3000J单据同步','erp/bs3000j/trade_list','1','1','0','1','1');",
		"alter table erp_config add column `erp_params` text NOT NULL COMMENT 'erp密钥参数'",
		"alter table erp_config add column `online_time` date NOT NULL COMMENT 'erp上线时间'",
		"alter table erp_config add column `trade_sync` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '单据同步: 0不 启用, 1 启用'",
		"INSERT INTO `sys_schedule` VALUES ('40', 'erp_item_download_cmd', '档案同步', '', '', '0', '3', '仅支持BSERP2和BS3000J产品对接，档案同步的内容包括：商品基本信息、商品颜色、商品尺码、大类、季节、品牌等（商品条形码需要在系统人工操作生成）；此服务240分钟运营一次。', '{\"action\":\"sys\\/erp_config\\/item_download_cmd\"}', '', '0', '0', '0', '14400', '0', 'api', '', '0', '0');",
		"INSERT INTO `sys_schedule` VALUES ('41', 'erp_item_inv_update_cmd', '库存获取、覆盖系统库存', '', '', '0', '3', '仅支持BSERP2和BS3000J产品对接，此服务包含两个功能项：1.库存获取，通过接口获取ERP最新的商品库存2.库存覆盖，即将获取的ERP库存，覆盖系统库存此服务60分钟运营一次。', '{\"action\":\"sys\\/erp_config\\/item_inv_update_cmd\"}', '', '0', '0', '0', '3600', '0', 'api', '', '0', '0');",
		"INSERT INTO `sys_schedule` VALUES ('44', 'erp_trade_upload_cmd', '单据同步', '', '', '0', '3', '仅支持BSERP2和BS3000J产品对接，系统的网络订单和有退货的售后服务单，上传到ERP。此服务120分钟运营一次。', '{\"action\":\"sys\\/erp_config\\/trade_upload_cmd\"}', '', '0', '0', '0', '7200', '0', 'api', '', '0', '0');",
		"CREATE TABLE `api_bs3000j_categories` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `DLDM` varchar(50) NOT NULL COMMENT '分类代码',
		  `DLMC` varchar(100) DEFAULT NULL COMMENT '分类名称',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `DLDM` (`DLDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bs3000j_brands` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `PPDM` varchar(50) NOT NULL COMMENT '品牌代码',
		  `PPMC` varchar(100) DEFAULT NULL COMMENT '品牌名称',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `PPDM` (`PPDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bs3000j_item_quantity` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `efast_store_code` varchar(50) NOT NULL COMMENT 'efast仓库代码',
		  `CKDM` varchar(50) NOT NULL COMMENT '仓库代码',
		  `KWDM` varchar(50) NOT NULL COMMENT '库位代码',
		  `SPDM` varchar(50) NOT NULL COMMENT '商品代码',
		  `GG1DM` varchar(50) NOT NULL COMMENT '颜色代码',
		  `GG2DM` varchar(50) NOT NULL COMMENT '尺码代码',
		  `SL` int(8) NOT NULL DEFAULT '0' COMMENT '数量',
		  `SL1` int(8) DEFAULT '0' COMMENT '数量1',
		  `IDRank` varchar(5) DEFAULT NULL,
		  `updated` datetime NOT NULL COMMENT '最后更新时间 ',
		  `efast_update` datetime DEFAULT NULL COMMENT 'efast库存更新时间',
		  `update_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '库存更新状态',
		  PRIMARY KEY (`id`),
		  KEY `efast_store_code` (`efast_store_code`),
		  UNIQUE KEY `ckdm_spdm_gg1dm_gg2dm` (`erp_config_id`,`CKDM`,`SPDM`,`GG1DM`,`GG2DM`),
		  KEY `erp_config_id` (`erp_config_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bs3000j_item` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `SPDM` varchar(50) NOT NULL COMMENT '商品编号',
		  `SPMC` varchar(200) DEFAULT NULL COMMENT '商品名称',
		  `BZSJ` decimal(10,2) DEFAULT NULL COMMENT '标准售价',
		  `CKJ_IN` decimal(10,2) DEFAULT NULL COMMENT '整调价',
		  `BYZD3` varchar(50) DEFAULT NULL COMMENT '品牌',
		  `BYZD4` varchar(50) DEFAULT NULL COMMENT '分类',
		  `BYZD5` varchar(50) DEFAULT NULL COMMENT '季节',
		  `BYZD8` varchar(50) DEFAULT NULL COMMENT '年份',
		  `DWMC` varchar(50) DEFAULT NULL COMMENT '单位名称',
		  `TZSY` varchar(50) DEFAULT NULL,
		  `IDRanK` varchar(50) DEFAULT NULL,
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `SPDM` (`SPDM`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bs3000j_years` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `DLDM` varchar(50) NOT NULL COMMENT '年份代码',
		  `DLMC` varchar(100) DEFAULT NULL COMMENT '年份名称',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `DLDM` (`DLDM`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='年份档案表';",
		"CREATE TABLE `api_bs3000j_sizes` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `GGDM` varchar(50) NOT NULL COMMENT '尺码代码',
		  `GGMC` varchar(100) DEFAULT NULL COMMENT '尺码名称',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `GGDM` (`GGDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bs3000j_seasons` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `JJDM` varchar(50) NOT NULL COMMENT '季节代码',
		  `JJMC` varchar(100) DEFAULT NULL COMMENT '季节名称',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `JJDM` (`JJDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bs3000j_item_size` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `SPDM` varchar(50) NOT NULL COMMENT '商品代码',
		  `GGDM` varchar(50) NOT NULL COMMENT '尺码代码',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `SPDMGGDM` (`SPDM`,`GGDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bs3000j_item_color` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `SPDM` varchar(50) NOT NULL COMMENT '商品代码',
		  `GGDM` varchar(50) NOT NULL COMMENT '颜色代码',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `SPDMGGDM` (`SPDM`,`GGDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bs3000j_colors` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `GGDM` varchar(50) NOT NULL COMMENT '颜色代码',
		  `GGMC` varchar(100) DEFAULT NULL COMMENT '规格描述',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `GGDM` (`GGDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `erp_goods_inv_log` (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
		  `efast_store_code` varchar(20) NOT NULL DEFAULT '' COMMENT 'efast仓库代码',
		  `barcode` varchar(20) NOT NULL DEFAULT '' COMMENT '商品条码',
		  `sku` varchar(20) NOT NULL DEFAULT '' COMMENT '系统SKU码',
		  `goods_code` varchar(20) NOT NULL,
		  `spec1_code` varchar(20) NOT NULL,
		  `spec2_code` varchar(20) NOT NULL,
		  `prev_num` int(11) NOT NULL DEFAULT '0' COMMENT '原先库存数量',
		  `after_num` tinyint(11) NOT NULL DEFAULT '0' COMMENT '更新后的库存数量',
		  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
		  PRIMARY KEY (`id`),
		  KEY `barcode` (`barcode`) USING BTREE,
		  KEY `sku` (`sku`) USING BTREE,
		  KEY `efast_store_code` (`efast_store_code`) USING BTREE,
		  KEY `idx_efast_store_code_barcode` (`efast_store_code`,`barcode`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;",
		"CREATE TABLE `api_bs3000j_trade` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号/退单号)',
		  `deal_code` varchar(80) NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)',
		  `deal_code_list` varchar(200) NOT NULL DEFAULT '' COMMENT '平台交易号列表',
		  `order_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '单据类型 1 销售订单 2销售退单',
		  `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
		  `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
		  `upload_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '上传状态 0未上传 1已上传 2上传失败',
		  `upload_time` datetime NOT NULL COMMENT '上传时间',
		  `upload_msg` varchar(255) DEFAULT NULL COMMENT '上传失败原因',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `sell_record_code` (`sell_record_code`,`order_type`),
		  KEY `upload_time` (`upload_time`),
		  KEY `shop_code` (`shop_code`),
		  KEY `store_code` (`store_code`),
		  KEY `order_type` (`order_type`),
		  KEY `deal_code` (`deal_code`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='erp上传单据';",
		
);
$u['FSF-1592'] = array(
		"INSERT INTO `sys_schedule` VALUES ('42', 'fx_refund_download_cmd', '淘宝分销退单下载', '', '', '0', '1', '启用后，系统将自动从淘宝拉取各店铺分销的退单信息', '{\"action\":\"api\\/order\\/fx_refund_download_cmd\"}', '', '0', '0', '0', '900', '0', 'api', '', '0', '0');",
"CREATE TABLE `api_taobao_fx_refund` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'taobao_refunds_trade的主键,自增',
  `shop_code` varchar(100) NOT NULL COMMENT '商店code',
  `purchase_order_id` varchar(50) NOT NULL COMMENT '主采购单id',
  `sub_order_id` varchar(50) NOT NULL COMMENT '退款子单的id',
  `refund_create_time` datetime DEFAULT NULL COMMENT '退款创建时间',
  `modified` datetime DEFAULT NULL COMMENT '退款修改时间',
  `is_return_goods` tinyint(1) DEFAULT NULL COMMENT '是否退货',
  `refund_status` tinyint(2) DEFAULT NULL,
  `refund_fee` varchar(30) DEFAULT NULL COMMENT '退款的金额',
  `pay_sup_fee` varchar(30) DEFAULT NULL COMMENT '支付给供应商的金额',
  `refund_reason` varchar(255) DEFAULT NULL COMMENT '退款原因',
  `refund_desc` varchar(255) DEFAULT NULL COMMENT '退款说明',
  `supplier_nick` varchar(255) DEFAULT NULL COMMENT '供应商nick',
  `distributor_nick` varchar(255) DEFAULT NULL COMMENT '分销商nick',
    `refund_record_code` varchar(30) NOT NULL DEFAULT '' COMMENT '业务系统单据编号(售后服务单号)',
  `change_remark` varchar(200) DEFAULT '' COMMENT '转单日志',
  `is_change` tinyint(1) DEFAULT '0' COMMENT '0：未转单 1：已转单',
    `insert_time` datetime DEFAULT NULL COMMENT '系统下载退单时间',
  `updated_time` datetime DEFAULT NULL COMMENT '系统更新退单时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_order_id` (`sub_order_id`),
  KEY `purchase_order_id` (`purchase_order_id`),
  KEY `shop_code` (`shop_code`) USING BTREE,
  KEY `refund_create_time` (`refund_create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
",
"INSERT INTO `sys_action` VALUES ('8030300', '8030000', 'url', '淘宝分销退单', 'api/api_taobao_fx_refund/do_list', '3', '1', '0', '1','0');",
"INSERT INTO `sys_schedule` VALUES ('43', 'auto_trans_api_fenxiao_refund', '自动转淘宝分销退单', '', '', '0', '0', '自动转退单针对发货前的订单进行拦截设问,针对发货后的订单自动生成售后服务单', '{\"app_act\":\"cli\\/auto_trans_api_fenxiao_refund\"}', 'webefast/web/index.php', '0', '0', '0', '900', '0', 'sys', '', '0', '0');",
    


);

$u['FSF-1595'] = array(
		"insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
            values('','part_deliver_goods','oms_taobao','交易部分发货回写','radio','[\"关闭\",\"开启\"]','1','0','1-开启 0-关闭','2015-08-20 17:21:36','拆弹情况，如果部分商品发货，则网单回写只针对发货的商品进行发货状态回写，默认开启');",
);
