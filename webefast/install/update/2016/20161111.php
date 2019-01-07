<?php

$u['799'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4030115', '4030100', 'act', '强制改价', 'oms/sell_return/update_abjust_money', '1', '1', '0', '1', '0');"
);

$u['775'] = array(
    "INSERT INTO `sys_params` (`param_code`,`parent_code`,`param_name`,`type`) VALUES('jiazhuang_trade','0','家装行业','group');",
    "INSERT INTO `sys_params` (`param_code`,`parent_code`,`param_name`,`type`,`form_desc`,`value`,`sort`,`remark`) VALUES('jiazhuang_trade_shipping','jiazhuang_trade','家装行业发货回写','radio','[\"关闭\",\"开启\"]','0','0.00','1-开启 0-关闭');",
    "INSERT INTO `sys_params` (`param_code`,`parent_code`,`param_name`,`type`,`form_desc`,`value`,`sort`,`memo`) VALUES('jiazhuang_shop','jiazhuang_trade','家装店铺（淘宝）','select','','','0.00','请谨慎选择家装店铺，选择后支持家装店铺发货回写。');"
);

$u['785'] = array(
    "ALTER TABLE base_shop ADD COLUMN custom_code varchar(128) NOT NULL DEFAULT '' COMMENT '分销商代码';"
);

$u['792'] = array("ALTER TABLE crm_goods ADD inv_num int(10) not null comment'获取库存';");
$u['782'] = array(
//KIS菜单
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('13000000', '0', 'cote', '金蝶集成', 'api-kisdee-manage', '4', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('13010000', '13000000', 'group', '基础配置', 'kisdee-base', '1', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('13010100', '13010000', 'url', '授权配置', 'sys/kisdee_config/do_list', '1', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('13020000', '13000000', 'group', '集成接口', 'kisdee-api', '2', '1', '0', '0', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('13020100', '13020000', 'url', '零售日报', 'kis/kisdee/sell_daily_list', '1', '1', '0', '0', '1');",
    //仓库对应关系表结构完善
    "ALTER TABLE sys_api_shop_store MODIFY COLUMN `p_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '类型: 0-ERP, 1-WMS, 2-KIS, 3-SAP';",
    "ALTER TABLE sys_api_shop_store COMMENT '系统-API,仓库、店铺对应关系表'",
    //金蝶配置表
    "CREATE TABLE `kisdee_config` (
        `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `config_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '配置名称',
        `kis_server_url` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '业务类API路由URL,通过get_server_url获取,定时更新',
        `kis_netid` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '网络ID,通过get_server_url获取',
        `kis_method` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'API接口名称',
        `kis_ver` VARCHAR(10) NOT NULL DEFAULT '' COMMENT 'API协议版本,如:2.0',
        `kis_eid` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业号',
        `kis_custdata` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '应用参数,json格式',
        `kis_params` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '开发者参数,json格式',
        `config_status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '启用状态:0-停用;1-启用',
        `online_time` INT NOT NULL DEFAULT '0' COMMENT 'KIS上线时间',
        `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        PRIMARY KEY (`config_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='金蝶KIS配置';"
);

$u['781'] = array(
    "CREATE TABLE sap_config
(
	`sap_config_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
	`sap_config_name` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'sap配置名称',
	`online_time` date NOT NULL COMMENT 'sap上线时间',
  `sap_address` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'sap地址',
  `instance_number` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '实例编号',
	`client` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '客户端',
	`account` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '账号',
	`password` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '密码',
	`efast_store_code` varchar(128) DEFAULT '' COMMENT 'efast仓库代码',
	`sap_store_code` varchar(128) DEFAULT '' COMMENT 'sap仓库代码',
	PRIMARY KEY (`sap_config_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='sap配置';",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('14000000', '0', 'cote', 'SAP集成', 'api-sap-manage', '72', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('14010000', '14000000', 'group', '基础配置', 'api-sap-base', '80', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('14010100', '14010000', 'url', 'SAP配置', 'sys/sap_config/do_list', '1', '1', '0', '1', '1');",
    "CREATE TABLE sap_adjust_record
(
	`sap_adjust_record_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
	`record_code` varchar(64) DEFAULT '' COMMENT '编号',
	`mjahr` INT(10) NOT NULL  DEFAULT 0 COMMENT '凭证年度',
	`mblnr` VARCHAR(20) NOT NULL  DEFAULT '' COMMENT '物料凭证',
	`zeile` INT(10) NOT NULL DEFAULT 0 COMMENT '行科目',
	`cpudt_mkpf` INT NOT NULL DEFAULT '00000000' COMMENT '输入日期',
	`cputm_mkpf` time NOT NULL  DEFAULT '00:00:00' COMMENT '输入时间',
	`shkzg` VARCHAR(4) NOT NULL  DEFAULT '' COMMENT '借贷标示h加库存，s减库存',
	`matnr` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '物料号，对应条码',
	`werks` VARCHAR(10) NOT NULL  DEFAULT '' COMMENT '调出共厂',
	`lgort` VARCHAR(10) NOT NULL DEFAULT '' COMMENT '调出库存地点',
	`num` INT(13) NOT NULL DEFAULT 0 COMMENT '数量',
	`meins` VARCHAR(10) NOT NULL DEFAULT 0 COMMENT '单位',
	`umwrk` VARCHAR(10) NOT NULL DEFAULT '' COMMENT '调入工厂',
	`umlgo` VARCHAR(10) NOT NULL DEFAULT '' COMMENT '调入库存地点',
	`status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否处理成了调整单 0不是 1是',
	`download_date` datetime DEFAULT NULL COMMENT '下载时间',
	`handle_date` datetime DEFAULT NULL COMMENT '处理时间',
	`handle_info` VARCHAR(128) NOT NULL DEFAULT'' COMMENT '处理失败信息',
	`store_code`  varchar(128) DEFAULT '' COMMENT '系统仓库代码',
	`stm_record_code` varchar(64) DEFAULT '' COMMENT '库存调整单单号',
	PRIMARY KEY (`sap_adjust_record_id`),
	UNIQUE KEY `_key` (`record_code`) USING BTREE
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='sap数据中间表';",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('14020000', '14000000', 'group', '集成借口', 'api-sap-manage', '80', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('14020100', '14020000', 'url', '调整单单据', 'sys/sap_adjust_record/do_list', '1', '1', '0', '1', '1');"
);

$u['786'] = array(
    " UPDATE sys_role_manage_price SET `desc`='在采购管理以及商品进销存分析/商品列表进行控制，开启后，此角色对应用户可看到商品的进货价，其他用户显示****' WHERE manage_code='purchase_price'",
    " UPDATE sys_role_manage_price SET `desc`='在商品库存查询导出/商品进销存分析/商品列表进行控制，开启后，此角色对应用户可看到商品的成本价，其他用户显示****' WHERE manage_code='cost_price'"
);
$u['804'] = array(
    "DROP TABLE IF EXISTS `kisdee_trade`;",
    "CREATE TABLE `kisdee_trade` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `record_code` varchar(32) NOT NULL DEFAULT '' COMMENT '单据编号',
        `record_type` varchar(32) NOT NULL DEFAULT '' COMMENT '单据类型:sell_record-销售订单,sell_return-销售退单',
        `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '商店代码',
        `store_code` varchar(128) NOT NULL DEFAULT '' COMMENT '仓库代码',
        `quantity` int(10) NOT NULL DEFAULT '0' COMMENT '总数量',
        `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额',
        `express_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总运费',
        `upload_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '上传状态 0未上传 1已上传 2上传失败',
        `record_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '业务日期',
        `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '生成时间',
        `upload_time` int(11) NOT NULL DEFAULT '0' COMMENT '上传时间',
        `fail_cause` varchar(255) DEFAULT NULL COMMENT '上传失败原因',
        `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注(摘要)',
        PRIMARY KEY (`id`),
        UNIQUE KEY `uni_record_code_type` (`record_code`,`record_type`) USING BTREE,
        KEY `ix_upload_time` (`upload_time`) USING BTREE,
        KEY `ix_shop_code` (`shop_code`) USING BTREE,
        KEY `ix_store_code` (`store_code`) USING BTREE,
        KEY `ix_upload_status` (`upload_status`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='KIS零售日报';",

    "DROP TABLE IF EXISTS `kisdee_trade_record_detail`;",
    "CREATE TABLE `kisdee_trade_record_detail` (
        `detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `detail_no` int(11) DEFAULT NULL COMMENT '序号',
        `record_code` varchar(32) NOT NULL DEFAULT '' COMMENT '单据编号',
        `goods_code` varchar(64) NOT NULL DEFAULT '' COMMENT '商品代码',
        `sku` varchar(32) NOT NULL DEFAULT '' COMMENT 'sku',
        `num` int(10) NOT NULL DEFAULT '0' COMMENT '数量',
        `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
        PRIMARY KEY (`detail_id`),
        UNIQUE KEY `uni_record_code_no` (`record_code`,`detail_no`) USING BTREE,
        KEY `ix_record_code` (`record_code`) USING BTREE,
        KEY `ix_goods_code` (`goods_code`) USING BTREE,
        KEY `ix_sku` (`sku`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='KIS销售订单日报明细';",

    "DROP TABLE IF EXISTS `kisdee_trade_return_detail`;",
    "CREATE TABLE `kisdee_trade_return_detail` (
        `detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `detail_no` int(11) DEFAULT NULL COMMENT '序号',
        `record_code` varchar(32) NOT NULL DEFAULT '' COMMENT '单据编号',
        `goods_code` varchar(64) NOT NULL DEFAULT '' COMMENT '商品代码',
        `sku` varchar(30) DEFAULT '' COMMENT 'sku',
        `num` int(10) NOT NULL DEFAULT '0' COMMENT '数量',
        `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
        PRIMARY KEY (`detail_id`),
        UNIQUE KEY `uni_record_code_no` (`record_code`,`detail_no`) USING BTREE,
        KEY `ix_record_code` (`record_code`) USING BTREE,
        KEY `ix_goods_code` (`goods_code`) USING BTREE,
        KEY `ix_sku` (`sku`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='KIS销售退单日报明细';"
);
$u['816'] = array(
    "alter table api_refund_detail modify column goods_barcode varchar(128) DEFAULT NULL COMMENT '平台SKU外部编码, 淘宝平台：outer_iid';",
    "alter table api_taobao_order modify column outer_iid varchar(128) DEFAULT NULL COMMENT '商家外部编码(可与商家外部系统对接)。外部商家自己定义的商品Item的id，可以通过taobao.items.custom.get获取商品的Item的信息';",
    "alter table report_base_goods_collect modify column goods_barcode varchar(128) DEFAULT '0' COMMENT '商品条码';",
    "alter table api_order_detail modify column goods_barcode varchar(128) DEFAULT NULL COMMENT '平台SKU外部编码, 淘宝平台：outer_iid';",
    "alter table api_goods_sku modify column goods_barcode varchar(128) DEFAULT '' COMMENT '外部网店自己定义的Sku编号 淘宝平台：outer_id';",
    "alter table wms_trade_quehuo_mx modify column barcode  varchar(128) NOT NULL COMMENT '条码' ;",
    "alter table wms_oms_order_lof modify column barcode varchar(128) NOT NULL DEFAULT '' COMMENT '商品条码';",
    "alter table wms_oms_order modify column barcode varchar(128) NOT NULL DEFAULT '' COMMENT '商品条码';",
    "alter table wms_goods_inv_log modify column barcode varchar(128) NOT NULL DEFAULT '' COMMENT '商品条码';",
    "alter table wms_goods_inv modify column barcode varchar(128) NOT NULL DEFAULT '' COMMENT '商品条码';",
    "alter table wms_b2b_order_lof modify column barcode varchar(128) NOT NULL DEFAULT '' COMMENT '商品条码';",
    "alter table wms_b2b_order_detail modify column barcode varchar(128) NOT NULL DEFAULT '' COMMENT '商品条码';",
    "alter table wms_b2b_order modify column barcode varchar(128) NOT NULL DEFAULT '' COMMENT '商品条码';",
    "alter table oms_sell_record_notice_detail modify column barcode varchar(128) NOT NULL DEFAULT '' COMMENT '条码';",
    "alter table oms_sell_record_detail modify column barcode varchar(128) NOT NULL DEFAULT '' COMMENT '条码';",
    "alter table goods_unique_code_log modify column barcode varchar(128) DEFAULT '' COMMENT '条形码';",
    "alter table base_spec2 modify column barcode varchar(128) DEFAULT '' COMMENT '条码对照码';",
    "alter table base_spec1 modify column barcode varchar(128) DEFAULT '' COMMENT '条码对照码';",
    "alter table api_taobao_order modify column outer_sku_id varchar(128) DEFAULT NULL COMMENT '外部网店自己定义的sku编号';"
);
