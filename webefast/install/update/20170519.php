<?php

$u['1301'] = array(
    "INSERT INTO `alipay_account_item` (`code`, `account_item`, `in_out_flag`) VALUES ('006', '聚划算佣金退款', '1');",
    "INSERT INTO `alipay_account_item` (`code`, `account_item`, `in_out_flag`) VALUES ('110', '聚划算佣金', '2');",
    "INSERT INTO `alipay_account_item` (`code`, `account_item`, `in_out_flag`) VALUES ('111', '花呗保证金退款', '2');",
    "INSERT INTO `alipay_account_item` (`code`, `account_item`, `in_out_flag`) VALUES ('112', '运费险-保费', '2');",
    "INSERT INTO `alipay_account_item` (`code`, `account_item`, `in_out_flag`) VALUES ('113', '分销维权', '2');",
    "INSERT INTO `alipay_account_item` (`code`, `account_item`, `in_out_flag`) VALUES ('114', '保证金退款', '2');",
    "ALTER TABLE alipay_account_item ADD UNIQUE KEY `code` (`code`);"
);

$u['1311'] = array(
    "ALTER TABLE api_taobao_fx_product_sku ADD COLUMN `sku_status` int(4) DEFAULT '1' COMMENT '平台SKU状态 0：删除 1：正常';",
);

$u['1310'] = array(
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('taobao_goods_download_cmd', '淘宝商品下载', '', 'taobao', '0', '1', '', '{\"action\":\"api / goods / taobao_goods_download_cmd\"}', '', '0', '0', '0', '180', '0', 'api', '', '0', NULL, '0');",
    "UPDATE sys_schedule SET `name`='商品下载（非淘系）' WHERE code='goods_download_cmd';",
    "update sys_schedule r2,(SELECT status from sys_schedule where code='goods_download_cmd') AS r1 SET r2.`status`=1 WHERE r2.code='taobao_goods_download_cmd' AND r1.status=1;",
);

$u['bug_1292'] = array(
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('weipinhui_inv_upload_cmd', '唯品会商品库存同步', '', 'weipinhui', '0', '1', '启用后，系统将根据库存策略，把商品的efast可用库存，自动同步到唯品会平台', '{\"action\":\"api / goods / weipinhui_inv_upload_cmd\"}', '', '0', '0', '0', '60', '0', 'api', '', '0', NULL, '0');",
    "update sys_schedule set loop_time=900,status=0 where code='weipinhuijit_getOccupiedOrders_cmd';",
);

$u['1299'] = array(
    "INSERT INTO `sys_print_templates` ( `print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace_key`, `template_body_replace`, `template_body_default`, `new_old_type`) VALUES ('barcode_lodop', '条码打印模板(新)', NULL, '4', '1', '1', '6', '600', '900', '无', '', 'LODOP.PRINT_INITA(\"1mm\",\"6mm\",800,600,\"\");\r\nLODOP.SET_PRINT_PAGESIZE(0,600,900,\"\");\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",12,19,150,25,\"执行标准\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",35,19,150,25,\"条码描述\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",59,19,150,25,\"品牌：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"brand_name\",59,54,150,25,c[\"brand_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",82,19,150,25,\"品名：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"goods_name\",82,54,150,25,c[\"goods_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",103,19,150,25,\"货号：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"goods_code\",103,54,150,25,c[\"goods_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",127,19,150,25,\"尺码：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"spec2_code\",127,54,150,25,c[\"spec2_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",151,19,150,25,\"颜色：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"spec1_code\",151,54,150,25,c[\"spec1_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",175,19,150,25,\"零售价：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"price\",175,66,150,25,c[\"price\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_BARCODE(207,21,200,80,\"128B\",c[\"690123456789\"]);\r\n', NULL, '[]', 'LODOP.PRINT_INITA(\"1mm\",\"6mm\",800,600,\"\");\r\nLODOP.SET_PRINT_PAGESIZE(0,600,900,\"\");\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",12,19,150,25,\"执行标准\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",35,19,150,25,\"条码描述\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",59,19,150,25,\"品牌：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"brand_name\",59,54,150,25,c[\"brand_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",82,19,150,25,\"品名：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"goods_name\",82,54,150,25,c[\"goods_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",103,19,150,25,\"货号：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"goods_code\",103,54,150,25,c[\"goods_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",127,19,150,25,\"尺码：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"spec2_code\",127,54,150,25,c[\"spec2_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",151,19,150,25,\"颜色：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"spec1_code\",151,54,150,25,c[\"spec1_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",175,19,150,25,\"零售价：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"price\",175,66,150,25,c[\"price\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_BARCODE(207,21,200,80,\"128B\",c[\"690123456789\"]);\r\n', '0');",
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'barcode_template', 'sys_set', 'S006-010 条码模版打印选择', 'radio', '[\"条码模版\",\"条码模版（新）\"]', '1', '0.00', '1-条码模版（新） 0-条码模版', '', '新模版支持图片上传以及固定文本打印');"
);

$u['1322'] = array(
    "ALTER TABLE pur_advide_detail ADD  `sale_two_month_num` float(11,2) DEFAULT '0.00' COMMENT '60天销售数量';",
    "ALTER TABLE pur_advide_detail ADD  `sale_two_month_num_all` int(11) DEFAULT '0';",
    "ALTER TABLE pur_advide_detail ADD  `sale_three_month_num` float(11,2) DEFAULT '0.00' COMMENT '90天销售数量';",
    "ALTER TABLE pur_advide_detail ADD  `sale_three_month_num_all` int(11) DEFAULT '0';",
    "ALTER TABLE pur_advide_detail modify column `sale_week_num_all` int(11) DEFAULT '0';",
    "ALTER TABLE pur_advide_detail modify column `sale_month_num_all` int(11) DEFAULT '0';",
);

$u['1202'] = array(
    "INSERT `sys_action` VALUES ('15020200', '15020000', 'url', 'ERP管理', 'erp/bserp/erp_do_list', '1', '1', '0', '1', '1');",
    "CREATE TABLE `bsapi_trade` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `record_code` varchar(32) NOT NULL DEFAULT '' COMMENT '单据编号',
        `record_type` varchar(32) NOT NULL DEFAULT '' COMMENT '单据类型:sell_record-销售订单,sell_return-销售退单',
        `api_type` varchar(16) NOT NULL DEFAULT '' COMMENT '对接接口名称 bserp2 bserp3 bs3000j',
        `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '商店代码',
        `store_code` varchar(128) NOT NULL DEFAULT '' COMMENT '仓库代码',
        `is_fenxiao` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否分销单 0 不是 1 是',
        `quantity` int(10) NOT NULL DEFAULT '0' COMMENT '总数量',
        `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额',
        `express_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总运费',
        `record_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '业务日期',
        `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '生成时间',
        `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注(摘要)',
        PRIMARY KEY (`id`),
        UNIQUE KEY `uni_record_code_type` (`record_code`,`record_type`) USING BTREE,
        KEY `ix_shop_code` (`shop_code`) USING BTREE,
        KEY `ix_store_code` (`store_code`) USING BTREE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='百胜api零售日报主信息';",
    "CREATE TABLE `bsapi_trade_detail` (
        `detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `detail_no` int(11) DEFAULT NULL COMMENT '序号',
        `record_code` varchar(32) NOT NULL DEFAULT '' COMMENT '单据编号',
        `record_type` varchar(32) NOT NULL DEFAULT '' COMMENT '单据类型:sell_record-销售订单,sell_return-销售退单',
        `goods_code` varchar(64) NOT NULL DEFAULT '' COMMENT '商品代码',
        `sku` varchar(32) NOT NULL DEFAULT '' COMMENT 'sku',
        `num` int(10) NOT NULL DEFAULT '0' COMMENT '数量',
        `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
        PRIMARY KEY (`detail_id`),
        UNIQUE KEY `record_code_no` (`record_code`,`detail_no`) USING BTREE,
        KEY `ix_record_code` (`record_code`) USING BTREE,
        KEY `ix_goods_code` (`goods_code`) USING BTREE,
        KEY `ix_sku` (`sku`) USING BTREE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='百胜api零售日报明细';"
);

$u['1304'] = array(
    "INSERT INTO `sys_print_templates` (
	`print_templates_code`,
	`print_templates_name`,
	`company_code`,
	`type`,
	`is_buildin`,
	`offset_top`,
	`offset_left`,
	`paper_width`,
	`paper_height`,
	`printer`,
	`template_val`,
	`template_body`,
	`template_body_replace_key`,
	`template_body_replace`,
	`template_body_default`,
	`new_old_type`
)
VALUES
	(
		'wbm_store_out_record_goods',
		'销货单商品打印',
		NULL,
		'1',
		'0',
		'0',
		'0',
		'100',
		'50',
		'',
		'{\"conf\":\"wbm_store_out_record_goods\",\"page_next_type\":\"1\",\"css\":\"tprint_report\",\"page_size\":\"9\",\"report_top\":\"5\",\"report_left\":\"10\"}',
		'<div id=\"report\" style=\'height: 150px;margin: 0 auto;padding-top: 5mm;text-align: center;width: 100%;\'>\r\n	<div id=\"report_top\" class=\"group\" title=\"报表头\" style=\'margin:auto;font-size:8px;height:50px;\'>\r\n		<div style=\"width:100%\" id=\"row_4\" class=\"row border\">\r\n			<div style=\"font-size:22px;width:100px;\" id=\"column_9\" class=\"column\">{@送货仓库}</div>\r\n			<div style=\"margin-left:20px;font-size:22px;width:60px;\" id=\"column_12\" class=\"column\">PO：</div>\r\n			<div style=\"font-size:22px;width:100px;\" id=\"column_13\" class=\"column\">{@PO号}</div>\r\n		</div>\r\n	</div>\r\n	<div type=\"table\" nodel=\"1\" id=\"report_table_body\" class=\"group\" title=\"表格\" style=\"height:100px;\">\r\n		<table id=\"table_1\" class=\"table\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\'border:none;font-size:8px;width:100%\'>\r\n		<tr></tr>\r\n			<!--detail_list-->\r\n		</table>\r\n	</div>\r\n</div>',
		NULL,
		'<tr>\r\n<td style=\"border:none\" class=\"td_detail\">\r\n<div style=\"font-size:30px;width:100%\" class=\"td_column\" id=\"column_td_68\">{#条码}</div>\r\n<div style=\"font-size:20px;float:left;\" class=\"td_column\" id=\"column_td_71\">数量：</div>\r\n<div style=\"font-size:20px;float:left;margin-left:10px;\" class=\"td_column\" id=\"column_td_69\">{#通知数}</div>\r\n<div style=\"font-size:20px;float:left;margin-left:10px;\" class=\"td_column\" id=\"column_td_70\">{#下单日期}</div>\r\n</td>\r\n</tr>',
		'',
		'0'
	);"
);

$u['1321'] = array(
    "delete from sys_user_pref where iid='rpt/sell_return_after_sell_return';",
);


$u['1319'] = array(
    "update sys_print_templates set print_templates_name='批发销货单模版' where print_templates_code='wbm_store_out_new';",
    "update sys_print_templates set print_templates_name='采购入库单模版' where print_templates_code='pur_purchaser_new';",
    "update sys_print_templates set print_templates_name='波次单模版' where print_templates_code='oms_waves_record_new';",
    "update sys_print_templates set print_templates_name='采购退货单模版' where print_templates_code='pur_return_new';",
    "update sys_print_templates set print_templates_name='批发退货单模版' where print_templates_code='wbm_return_new';",
    "update sys_print_templates set print_templates_name='批发销货通知单模版' where print_templates_code='wbm_notice_store_out_new';",
    "update sys_print_templates set new_old_type = 1 where print_templates_code='barcode';",
    "UPDATE `sys_params` SET `memo`='开启后，系统以Clodop云打印控件打印，若未安装，打印时会提示安装新控件，未开启以老控件lodop打印，<a id=\'down_lodop\' href=\'\'>点击下载</a>' WHERE `param_code`='clodop_print';",
);

$u['1329'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7010115', '7010001', 'url', '多包裹验货', 'oms/deliver_package/multi_examine', '29', '1', '0', '1', '0');",
    "ALTER TABLE `oms_deliver_record_package` ADD COLUMN `goods_num` INT (11) DEFAULT '0' COMMENT '包裹商品数量';",
    "ALTER TABLE `oms_deliver_record_package` ADD COLUMN `scan_num` INT (11) DEFAULT '0' COMMENT '包裹扫描商品数量';",
    "ALTER TABLE `oms_deliver_record_package` ADD COLUMN `packet_status` TINYINT (1) DEFAULT '0' COMMENT '包裹封包状态:0-已封包;1-未封包';",
    "ALTER TABLE `oms_deliver_record_package` ADD COLUMN `packet_time` INT (11) DEFAULT '0' COMMENT '封包时间';",
    "ALTER TABLE `oms_deliver_record_package` ADD COLUMN `print_status` TINYINT (1) DEFAULT '0' COMMENT '包裹打印状态:0-未打印;1-已打印';",
    "ALTER TABLE `oms_deliver_record_package` ADD COLUMN `print_time` INT (11) DEFAULT '0' COMMENT '打印时间';",
    "ALTER TABLE `oms_deliver_record_package` ADD COLUMN `is_multi_examine` TINYINT (1) DEFAULT '0' COMMENT '多包裹验货添加：0-否;1-是';",
    "ALTER TABLE `oms_deliver_record_package` ADD COLUMN `lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间';",
    "CREATE TABLE `oms_deliver_package_detail` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`package_record_id` INT (11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '包裹单ID',
        `sell_record_code` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '订单编号',
	`package_no` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '包裹单序号',
	`sku` VARCHAR (128) NOT NULL DEFAULT '' COMMENT 'sku',
	`goods_num` INT (11) NOT NULL DEFAULT '0' COMMENT '商品数量',
	`scan_num` INT (11) NOT NULL DEFAULT '0' COMMENT '扫描商品数量',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_code` (`sell_record_code`,`package_no`, `sku`) USING BTREE,
	KEY `ind_package_no` (`package_no`) USING BTREE,
	KEY `ind_sku` (`sku`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '发货订单包裹明细表';"
);
