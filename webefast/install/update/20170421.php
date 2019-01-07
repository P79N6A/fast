<?php

$u['1232'] = array(
    "UPDATE sys_params  SET param_name = '启用商品子条码'  WHERE param_code='goods_sub_barcode';",
    "UPDATE sys_params  SET parent_code='sys_set'  WHERE param_code='goods_sub_barcode';",
    "UPDATE sys_params  SET memo='默认关闭，开启后，商品库存查询/波次单导出支持商品子条码'  WHERE param_code='goods_sub_barcode';",
);


$u['1228'] = array(
    "CREATE TABLE base_goods_log
(
	`base_goods_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(64) DEFAULT '' COMMENT '用户ID',
  `user_code` varchar(64) DEFAULT '' COMMENT '用户代码',
	`add_time` INT DEFAULT NULL COMMENT '新增时间',
  `operation_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作名称',
  `operation_note` mediumtext NOT NULL COMMENT '操作描述',
	`goods_id` int(11) NOT NULL DEFAULT 0 COMMENT '商品id',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`base_goods_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品操作日志';"
);

$u['1236'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8080400', '8080000', 'url', '分销品牌过滤', 'fx/goods_filter/do_list', '4', '1', '0', '1', '0');"
);

$u['1204'] = array(
        "CREATE TABLE `mid_api_record` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `mid_code` varchar(128) DEFAULT NULL,
      `api_product` varchar(128) DEFAULT NULL,
      `api_name` varchar(128) DEFAULT NULL,
      `start_time` datetime DEFAULT NULL COMMENT '上传请求开始时间',
      `end_time` datetime DEFAULT NULL COMMENT '上传请求结束时间',
      `request_data` text COMMENT '请求数据',
      `last_api_time` varchar(128) DEFAULT NULL COMMENT '接口返回时间戳，下次掉用使用',
      `api_request_time` int(11) DEFAULT '0' COMMENT '接口请求时间',
      `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `_key` (`mid_code`,`api_name`) USING BTREE,
      KEY `mid_code` (`mid_code`),
      KEY `api_product` (`api_product`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ",
        "CREATE TABLE `mid_archive` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `mid_code` varchar(128) DEFAULT NULL,
      `api_product` varchar(128) DEFAULT NULL,
      `type_code` varchar(128) DEFAULT '' COMMENT '数据类型',
      `api_code` varchar(128) DEFAULT '' COMMENT '接口对应数据CODE',
      `api_name` varchar(128) DEFAULT '' COMMENT '接口对应数据名称',
      `sys_code` varchar(128) DEFAULT '' COMMENT '系统对应code',
      `goods_code` varchar(128) DEFAULT NULL,
      `spec1_code` varchar(128) DEFAULT NULL,
      `spec2_code` varchar(128) DEFAULT NULL,
      `api_update_time` datetime DEFAULT NULL COMMENT '接口更新时间',
      `sys_update_time` datetime DEFAULT '1970-01-01 00:00:00',
      `down_time` datetime DEFAULT NULL COMMENT '下载时间',
      `api_json_data` text COMMENT '接口凡是数据',
      PRIMARY KEY (`id`),
      UNIQUE KEY `_key` (`mid_code`,`type_code`,`api_code`) USING BTREE,
      KEY `mid_code` (`mid_code`),
      KEY `type_code` (`type_code`),
      KEY `api_product` (`api_product`),
      KEY `sys_code` (`sys_code`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


    ",
        "CREATE TABLE `mid_goods_inv` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
      `mid_code` varchar(128) DEFAULT NULL,
      `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT 'efast仓库代码',
      `api_code_type` varchar(128) NOT NULL DEFAULT '' COMMENT 'barcode,sku',
      `api_code` varchar(128) NOT NULL DEFAULT '' COMMENT '商品条码',
      `sku` varchar(128) NOT NULL DEFAULT '' COMMENT 'sku',
      `goods_code` varchar(128) NOT NULL DEFAULT '' COMMENT '商品编码',
      `spec1_code` varchar(128) NOT NULL DEFAULT '' COMMENT '规格1',
      `spec2_code` varchar(128) NOT NULL DEFAULT '' COMMENT '规格2',
      `num` int(11) NOT NULL DEFAULT '0' COMMENT '商品数量',
      `is_sync` tinyint(11) NOT NULL DEFAULT '0' COMMENT '是否要同步到系统库存 1是需要同步 0是无需同步',
      `is_success` tinyint(11) NOT NULL DEFAULT '0' COMMENT '是否同步成功 1为成功 0是不成功',
      `sync_err` varchar(20) NOT NULL DEFAULT '' COMMENT '同步失败原因',
      `sync_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '库存同步到efast的时间',
      `down_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '下载库存时间',
      `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
      PRIMARY KEY (`id`),
      UNIQUE KEY `idx_efast_store_code_barcode` (`store_code`,`api_code`) USING BTREE,
      KEY `api_code` (`api_code`) USING BTREE,
      KEY `store_code` (`store_code`) USING BTREE
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ",
);

$u['1314'] = array(
    "ALTER TABLE `oms_sell_record` ADD COLUMN `is_receive` tinyint(3) DEFAULT '0' COMMENT '交接状态(1:交接成功 0:未交接 -1:交接失败)';",
);
$u['1315'] = array(
    "INSERT INTO `sys_action` VALUES ('91020206','7010001','url','包裹快递交接','oms/package_delivery_receive/do_list','30','1','0','1','0');",
);

$u['1258'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4030116', '4030100', 'act', '导出', 'oms/sell_return/export_after_service_list', '8', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7020101', '7020100', 'act', '导出', 'oms/sell_return/export_package_list', '0', '1', '0', '1', '0');",

);

$u['bug_1172'] = array(
	"ALTER TABLE base_shop ADD `taobao_shop_code` varchar(40) NOT NULL COMMENT '商店代码';",
);
$u['1152'] = array (
    "ALTER TABLE fx_appoint_goods MODIFY COLUMN fx_rebate DECIMAL(3,2) NOT NULL DEFAULT '0.00' COMMENT '指定分销商折扣';"
);


$u['1261'] = array(
		"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020701', '4020700', 'act', '导出', 'oms/sell_record/export_pending_list', '8', '1', '0', '1', '0');",
);

$u['1230_bug'] = array(
	"insert into sys_action values('3040101','3040100','act','一键清除','crm/activity/do_delete',0,1,0,1,0);"
);

$u['1270'] = array(
	"delete from base_sale_channel where sale_channel_code in ('vjia','yougou','m18','coo8','scn','weigou');",
);

$u['bug_1222'] = array(
	"update sys_params set parent_code='app' where param_code='clodop_print';",
        "ALTER TABLE sys_print_templates ADD new_old_type tinyint(3) DEFAULT '0' COMMENT'0：不区分,1：旧模板,2：新模板';",
        "update sys_print_templates set new_old_type=1 where print_templates_code in ('oms_waves_record','wbm_store_out','pur_return','wbm_return','wbm_notice_store_out','pur_purchaser','send_record_flash');",
        "update sys_print_templates set new_old_type=2 where print_templates_code in ('oms_waves_record_new','wbm_store_out_new','pur_return_new','wbm_return_new','wbm_notice_store_out_new','pur_purchaser_new');",
        "update sys_params set memo='开启后，系统以Clodop云打印控件打印，若未安装，打印时会提示安装新控件，未开启以老控件lodop打印' where param_code='clodop_print';",
);

$u['bug_1231'] = array("UPDATE `sys_schedule` SET `type`=1, `desc`='启用后，系统将自动从淘宝平台，拉取各店铺的平台订单信息，默认1分钟执行一次' WHERE code='taobao_order_download_cmd';",
                       "UPDATE `sys_schedule` SET `type`=1, `desc`='启用后，系统将自动从淘宝平台，拉取各店铺的平台退单信息，默认1分钟执行一次' WHERE code='taobao_refund_download_cmd';",
                       "UPDATE `sys_schedule` SET `name`='订单下载（非淘系）', `desc`='启用后，系统将自动从非淘系平台，拉取各店铺的平台订单信息，默认15分钟执行一次' WHERE code='order_download_cmd';",
                       "UPDATE `sys_schedule` SET `name`='退单下载（非淘系）', `desc`='启用后，系统将自动从非淘系平台，拉取各店铺的平台退单信息，默认15分钟执行一次' WHERE code='refund_download_cmd';"
);

$u['1226'] = array("INSERT INTO `sys_schedule` (`code`,`name`,`task_type_code`,`status`,`type`,`request`,`path`,`loop_time`,`task_module`)VALUES('cli_sync_jdwms_return','下载jdwms退货单','cli_sync_jdwms_return','0','2','{\"app_act\":\"wms/wms_mgr/sync_jdwms_return\"}','webefast/web/index.php','3600','sys');");