<?php

$u['1143'] = array(
    "UPDATE sys_role_manage_price SET `desc`='在商品库存查询导出/商品进销存分析/商品列表/库存调整单导出进行控制，开启后，此角色对应用户可看到商品的成本价，其他用户显示****' WHERE manage_code='cost_price';"
);

$u['1144'] = array(
    "UPDATE sys_action SET sort_order=21 WHERE action_id='8040000';",
);

$u['1151'] = array("INSERT INTO `sys_action`  VALUES ('7011100', '7010102', 'url', '取消发货', 'oms/waves_record/do_cancel', '70', '1', '0', '1', '0');");

$u['1149'] = array(
    //预售库存同步日志表
    "CREATE TABLE `op_presell_sync_log` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_code` VARCHAR (30) NOT NULL COMMENT '用户代码',
	`user_name` VARCHAR (30) NOT NULL COMMENT '用户名称',
	`plan_code` VARCHAR (64) NOT NULL COMMENT '预售计划代码',
	`shop_code` VARCHAR (128) NOT NULL COMMENT '店铺代码',
        `barcode` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '条码',
	`sku_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '平台sku_id',
	`num` INT (11) NOT NULL DEFAULT '0' COMMENT '同步数量',
	`result` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '同步结果',
	`insert_time` INT (11) NOT NULL DEFAULT '0' COMMENT '插入时间',
	PRIMARY KEY (`id`),
	KEY `ind_plan_code` (`plan_code`) USING BTREE,
	KEY `ind_shop_code` (`shop_code`) USING BTREE,
	KEY `ind_insert_time` (`insert_time`) USING BTREE,
	KEY `ind_barcode` (`barcode`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '预售同步日志表';",
    "ALTER TABLE op_presell_plan_detail MODIFY COLUMN `presell_num` INT (11) NOT NULL DEFAULT '0' COMMENT '预售数量';",
    "ALTER TABLE op_presell_plan_detail MODIFY COLUMN `sell_num` INT (11) NOT NULL DEFAULT '0' COMMENT '销售数量';",
    //预售拆单
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('tran_order_auto_presell_split', 'oms_property', '转单自动预售拆单', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', now(), '转单自动预售拆单');",
    "CREATE TABLE `op_presell_plan_pt_goods` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`pid` INT (11) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '预售明细id',
	`plan_code` VARCHAR (128) NOT NULL COMMENT '预售计划代码',
	`sku` VARCHAR (64) NOT NULL DEFAULT '' COMMENT '系统SKU',
	`shop_code` VARCHAR (128) NOT NULL COMMENT '店铺代码',
	`sku_id` varchar(255) DEFAULT '' COMMENT '平台sku_id',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_code` (`plan_code`, `sku`,`shop_code`,`sku_id`) USING BTREE,
	KEY `ind_pid` (`pid`) USING BTREE,
	KEY `ind_plan_code` (`plan_code`) USING BTREE,
	KEY `ind_sku` (`sku`) USING BTREE,
	KEY `ind_shop_code` (`shop_code`) USING BTREE,
	KEY `ind_sku_id` (`sku_id`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '预售商品关联的平台商品（需同步的）';",
    "ALTER TABLE api_goods_sku ADD COLUMN `pre_sync_status` TINYINT(1) NOT NULL DEFAULT '-1' COMMENT '预售禁止同步之前的商品库存同步状态';",
    "UPDATE sys_action SET action_code='op/presell/plan_sync_check' WHERE action_id=3050103;",
    //预售结束-自动服务
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('auto_res_pt_presell_goods', '自动还原平台商品预售信息', '', '', '1', '12', '', '{\"app_act\":\"op/presell/cli_res_pt_presell_goods\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '3600', '0', 'sys', '', '0', NULL, '0');",
);


$u['1154'] = array(
    "INSERT INTO `sys_action` (`action_id`,`parent_id`, `type`, `action_name`, `action_code`, `status`, `sort_order`,`appid`) VALUES ('4020343','4020300', 'url', '批量改商品', 'oms/order_opt/opt_alter_detail', '1', '4','1');"
);

$u['1126'] = array(
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace_key`, `template_body_replace`, `template_body_default`) VALUES ('pur_planned_record', '采购订单模板', NULL, '30', '0', '0', '0', '210', '150', '无', '{\"conf\":\"pur_planned_record\",\"page_next_type\":\"1\",\"css\":\"tprint_report\",\"page_size\":\"9\",\"report_top\":\"5\",\"report_left\":\"10\"}', '<div id=\"report\">\r\n	<div class=\"row border\" id=\"row_0\" style=\"height: 60px;\" nodel=\"1\">\r\n		<div class=\"column\" id=\"column_0\" style=\"width: 100%; height: 40px; text-align: center; line-height: 40px; font-size: 18px;\">采购订单</div>\r\n	</div>\r\n	<div style=\"height: 30px;width:600px;margin:0 auto;\" id=\"row_4\" class=\"row border\">\r\n		<div style=\'width:550px;\'>\r\n			<div class=\"row border\" id=\"row_9\" style=\"float:left;width:240px;\">\r\n				<div style=\"width:70px;height: 30px;\" id=\"column_9\" class=\"column\">采购单号：</div>\r\n				<div style=\"width:130px;height: 30px;\" id=\"column_12\" class=\"column\">{@采购单号}</div>\r\n			</div>\r\n			<div class=\"row border\" id=\"row_10\" style=\"float:left;width:240px;\">\r\n				<div style=\"width:70px;height: 30px;\" id=\"column_13\" class=\"column\">页码：</div>\r\n				<div style=\"width:130px;height: 30px;\" id=\"column_14\" class=\"column\">{@页码}</div>\r\n			</div>\r\n		</div>\r\n		<!--detail_list-->\r\n		\r\n	</div>\r\n</div>', NULL, '<div style=\"width:600px;clear:both;\">\r\n			<div style=\"width:320px;float:left;\">\r\n				<div class=\"td_detail\" style=\"float:left;width:320px;\">\r\n					<div style=\"width:300px;height:300px;float:left;\" id=\"column_td_15\" class=\"td_column\">{#图片地址}</div>\r\n				</div>\r\n			</div>\r\n\r\n			<div style=\"width:200px;float:left;\">\r\n				<div class=\"td_detail\" id=\"row_16\">\r\n					<div style=\"width:160px;\" id=\"column_td_16\" class=\"td_column\">{#编号}</div>\r\n				</div>\r\n\r\n				<div class=\"td_detail\" id=\"row_17\">\r\n					<div style=\"width:160px;\" id=\"column_td_17\" class=\"td_column\">货品描述：</div>\r\n					<div style=\"width:160px;\" id=\"column_td_18\" class=\"td_column\">{#货品描述}</div>\r\n				</div>\r\n			</div>\r\n</div>\r\n<div style=\"width:550px;clear:both;\">\r\n				<div class=\"td_detail\" id=\"row_19\" style=\"clear:both;\">\r\n					<div style=\"width:100px;float:left;\" id=\"column_td_19\" class=\"td_column\">商品编码：</div>\r\n					<div style=\"width:100px;float:left;\" id=\"column_td_20\" class=\"td_column\">{#商品编码}</div>\r\n				</div>\r\n\r\n				<div class=\"td_detail\" id=\"row_21\" style=\"clear:both;\">\r\n					<div style=\"width:100px;float:left;\" id=\"column_td_21\" class=\"td_column\">商品条形码：</div>\r\n					<div style=\"width:100px;float:left;\" id=\"column_td_22\" class=\"td_column\">{#商品条形码}</div>\r\n				</div>\r\n\r\n				<div class=\"td_detail\" id=\"row_23\" style=\"clear:both;\">\r\n					<div style=\"width:100px;float:left;\" id=\"column_td_23\" class=\"td_column\">数量：</div>\r\n					<div style=\"width:100px;float:left;\" id=\"column_td_24\" class=\"td_column\">{#数量}</div>\r\n				</div>\r\n</div>', '');",
);

$u['1175'] = array("UPDATE wbm_store_out_record AS r, wbm_store_out_record_detail AS d SET d.rebate = r.rebate WHERE r.record_code = d.record_code;");

$u['1169'] = array(
    //预售参数
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('presell_plan', 'op', '预售计划', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', now(), '开启后，请至运营->预售活动中维护预售活动以及预售商品，并可以同步预售数量至平台，识别预售订单。');",
    "INSERT INTO `sysdb`.`sys_action_extend` (`action_id`, `extend_code`) VALUES ('3050000', 'efast5_Standard') ON DUPLICATE KEY UPDATE extend_code=VALUES(extend_code),extend_code=VALUES(extend_code);",
    "INSERT INTO `sysdb`.`sys_action_extend` (`action_id`, `extend_code`) VALUES ('3050100', 'efast5_Standard') ON DUPLICATE KEY UPDATE extend_code=VALUES(extend_code),extend_code=VALUES(extend_code);",
    "INSERT INTO `sysdb`.`sys_action_extend` (`action_id`, `extend_code`) VALUES ('3050200', 'efast5_Standard') ON DUPLICATE KEY UPDATE extend_code=VALUES(extend_code),extend_code=VALUES(extend_code);"
);

$u['1162'] = array(
        "ALTER TABLE api_taobao_fx_trade ADD first_insert_time datetime DEFAULT NULL COMMENT '订单第一次写入系统时间';",
        "ALTER TABLE api_taobao_fx_trade ADD last_update_time datetime DEFAULT NULL COMMENT '订单在系统最后一次更新时间';",

        "ALTER TABLE api_taobao_fx_order ADD first_insert_time datetime DEFAULT NULL COMMENT '订单第一次写入系统时间';",
        "ALTER TABLE api_taobao_fx_order ADD last_update_time datetime DEFAULT NULL COMMENT '订单在系统最后一次更新时间';",

        "ALTER TABLE api_taobao_fx_product ADD first_insert_time datetime DEFAULT NULL COMMENT '商品第一次写入系统时间';",

        "ALTER TABLE api_taobao_fx_product_sku ADD first_insert_time datetime DEFAULT NULL COMMENT '商品第一次写入系统时间';",
        "ALTER TABLE api_taobao_fx_product_sku ADD last_update_time datetime DEFAULT NULL COMMENT '商品在系统最后一次更新时间';"
);

$u['1177'] = array(
    "ALTER TABLE `goods_diy` ADD COLUMN `price` decimal(20,3) DEFAULT NULL COMMENT '单价' AFTER `num`;",
    "ALTER TABLE `goods_combo` MODIFY COLUMN `status` int(4) COMMENT '0：停用 1：启用';"
);

$u['1150'] = array(
	"ALTER TABLE oms_return_package ADD return_buyer_memo varchar(255) NOT NULL DEFAULT '' COMMENT '退单说明（买家）';",
	"ALTER TABLE oms_return_package ADD return_remark varchar(255) NOT NULL DEFAULT '' COMMENT '退单备注';",
	"update oms_return_package rl inner join oms_sell_return r2 on rl.sell_return_code = r2.sell_return_code set rl.return_buyer_memo=r2.return_buyer_memo,rl.return_remark=r2.return_remark;",
);

$u['1177'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9070000', '9000000', 'group', '采购账务管理', 'pur_manage', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9070100', '9070000', 'url', '待付款列表', 'pur/accounts_payable/do_list', '10', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9070200', '9070000', 'url', '付款明细', 'pur/payment/do_list', '10', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9070201', '9070200', 'url', '作废', 'pur/payment/do_cancellation', '10', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9070300', '9070000', 'url', '付款统计', 'pur/payment/statistical', '10', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9070101', '9070100', 'url', '添加付款记录', 'pur/accounts/add_payment_detail', '1', '1', '0', '1', '0');",
    "CREATE TABLE pur_payment
(
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`serial_number` varchar(128) NOT NULL COMMENT '流水号',
	`planned_record_code` varchar(64) DEFAULT '' COMMENT '采购订单编号',
	`purchaser_record_code` varchar(64) DEFAULT '' COMMENT '采购入库单编号',
	`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:1-已付款;2-已作废',
	`detail_type`  tinyint(1) DEFAULT 0 COMMENT '1采购入库单生成；2采购订单生成',
	`supplier_code` varchar(128) DEFAULT '' COMMENT '供应商代码',
  `pay_type_code` varchar(128) NOT NULL DEFAULT '' COMMENT '支付方式代码',
	`payment_account` varchar(128) NOT NULL DEFAULT '' COMMENT '付款账户',
	`payment_time` int(11)  NOT NULL DEFAULT '0' COMMENT '付款时间',
	`money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
	`abstract` varchar(64) NOT NULL DEFAULT '' COMMENT '摘要',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',	
	`operator` varchar(255) NOT NULL DEFAULT '' COMMENT '操作人',
	`create_time` int(11)  NOT NULL DEFAULT '0' COMMENT '创建时间',
  `img_url` varchar(255) DEFAULT '' COMMENT '主图地址',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni_serial_number` (`serial_number`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='付款明细';",
    "ALTER TABLE pur_planned_record ADD COLUMN `is_payment` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '付款状态：0未付款 1已付款 2部分付款';",
    "ALTER TABLE pur_purchaser_record ADD COLUMN `is_payment` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '付款状态：0未付款 1已付款 2部分付款';",
    "ALTER TABLE pur_planned_record ADD COLUMN `payment_money` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '付款金额';",
    "ALTER TABLE pur_purchaser_record ADD COLUMN `payment_money` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '付款金额';",
    "ALTER TABLE pur_planned_record ADD COLUMN `is_notify_payment` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '通知付款：0未通知 1已通知';",
    "ALTER TABLE pur_order_record ADD COLUMN `is_notify_payment` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '通知付款：0未通知 1已通知';",
    "ALTER TABLE pur_purchaser_record ADD COLUMN `is_notify_payment` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '通知付款：0未通知 1已通知';",
);

$u['bug_1076'] = array(
    "ALTER TABLE oms_waves_record ADD INDEX idxu_store_code(`store_code`) USING BTREE;",
    "ALTER TABLE oms_waves_record ADD INDEX idxu_record_code(`record_code`) USING BTREE;",
    "ALTER TABLE oms_deliver_record ADD INDEX idxu_store_code(`store_code`) USING BTREE;",
    "ALTER TABLE oms_deliver_record ADD INDEX idxu_record_shop(`sell_record_code`,`shop_code`) USING BTREE;"
);

$u['bug_1095'] = array(
    "UPDATE sys_schedule SET `status` = 1 WHERE `code` = 'log_clean_up';",
); 

$u['1126_1'] = array(
	"UPDATE `sys_print_templates` SET `template_val`='{\"conf\":\"pur_planned_record\",\"page_next_type\":\"1\",\"css\":\"tprint_report\",\"page_size\":\"9\",\"report_top\":\"5\",\"report_left\":\"10\"}', `template_body`='<div id=\"report\">\r\n	<div title=\"报表头\" class=\"group\" id=\"report_top\" style=\"height:100px;clear:both;\">\r\n			<div class=\"row border\" id=\"row_0\" style=\"height: 60px;\" nodel=\"1\">\r\n			<div class=\"column\" id=\"column_0\" style=\"width: 100%; height: 40px; text-align: center; line-height: 40px; font-size: 18px;\">采购订单</div>\r\n			</div>\r\n			<div style=\'width:550px;margin:0 auto;\'>\r\n			<div class=\"row border\" id=\"row_9\" style=\"float:left;width:240px;\">\r\n				<div style=\"width:70px;height: 30px;\" id=\"column_9\" class=\"column\">采购单号：</div>\r\n				<div style=\"width:130px;height: 30px;\" id=\"column_12\" class=\"column\">{@采购单号}</div>\r\n			</div>\r\n			<div class=\"row border\" id=\"row_10\" style=\"float:left;width:240px;\">\r\n				<div style=\"width:70px;height: 30px;\" id=\"column_13\" class=\"column\">页码：</div>\r\n				<div style=\"width:130px;height: 30px;\" id=\"column_14\" class=\"column\">{@页码}</div>\r\n			</div>\r\n			</div>\r\n	</div>\r\n\r\n	<div title=\"表格\" class=\"group\" id=\"report_table_body\" type=\"table\" nodel=\"1\">	\r\n		<table class=\"table\" id=\"table_1\" style=\"height: 400px;width:400px;margin:0 10%;border:none;text-align:left;\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\r\n			\r\n			<!--detail_list-->\r\n		</table>\r\n		\r\n	</div>\r\n</div>', `template_body_replace_key`=NULL, `template_body_replace`='<tr>\r\n				<td class=\"td_title\" style=\"width: 300px;border:none;\" rowspan=\"3\"> \r\n					<div class=\"td_column\" id=\"column_th_69\" style=\"width: 300px; height: 300px;\">{#图片地址}</div>\r\n				</td>\r\n				<td class=\"td_title\" style=\"width: 100px;height:20px;border:none;\">\r\n					<div class=\"td_column\" id=\"column_th_70\" style=\"width: 100px;\">{#编号}</div>\r\n				</td>\r\n			</tr>\r\n			<tr>\r\n				<td class=\"td_title\" style=\"width: 100px;height:20px;border:none;\">\r\n					<div class=\"td_column\" id=\"column_th_71\" style=\"width: 100px;\">货品描述</div>\r\n				</td>\r\n			</tr>\r\n			<tr>\r\n				<td class=\"td_title\" style=\"width: 100px;border:none;\">\r\n					<div class=\"td_column\" id=\"column_th_72\" style=\"width: 100px; height: 20px; line-height: 20px;\">{#货品描述}</div>\r\n				</td>\r\n			</tr>\r\n			<tr>\r\n				<td class=\"td_title\" style=\"width: 60px;border:none;\">\r\n					<div class=\"td_column\" id=\"column_th_73\">商品编码:</div>\r\n				</td>\r\n				<td class=\"td_title\" style=\"width: 60px;border:none;\">\r\n					<div class=\"td_column\" id=\"column_th_74\">{#商品编码}</div>\r\n				</td>\r\n			</tr>\r\n			<tr>\r\n				<td class=\"td_title\" style=\"width: 60px;border:none;\">\r\n					<div class=\"td_column\" id=\"column_th_75\">商品条形码:</div>\r\n				</td>\r\n				<td class=\"td_title\" style=\"width: 60px;border:none;\">\r\n					<div class=\"td_column\" id=\"column_th_76\">{#商品条形码}</div>\r\n				</td>\r\n			</tr>\r\n			<tr>\r\n				<td class=\"td_title\" style=\"width: 60px;border:none;\">\r\n					<div class=\"td_column\" id=\"column_th_77\">数量:</div>\r\n				</td>\r\n				<td class=\"td_title\" style=\"width: 60px;border:none;\">\r\n					<div class=\"td_column\" id=\"column_th_78\">{#数量}</div>\r\n				</td>\r\n			</tr>' WHERE `print_templates_code`='pur_planned_record';",
);

$u['bug_1099'] = array(
	"UPDATE `sys_print_templates` SET  `template_body`='<div id=\"report\"><div id=\"report_top\" class=\"group\" title=\"报表头\"><div style=\"height: 50px;\" nodel=\"1\" id=\"row_0\" class=\"row border\"><div style=\"width: 400px; font-size: 24px; height: 50px; line-height: 50px; text-align: left;\" id=\"column_0\" class=\"column\">波次单</div><div style=\"height: 50px; line-height: 50px;\" id=\"column_87\" class=\"column\"></div><div style=\"height: 50px; line-height: 50px; width: 180px;\" id=\"column_88\" class=\"column\"><img src=\"assets/tprint/picon/barcode.png\" type=\"1\" class=\"barcode\" style=\"height:50px;width:180px;\"  title=\"{@波次号}\"></div></div><div style=\"height: 30px;\" id=\"row_4\" class=\"row border\"><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: left;\" id=\"column_9\" class=\"column\">波次号：</div><div style=\"width: 120px; text-align: left; height: 30px; line-height: 30px;\" id=\"column_12\" class=\"column\">{@波次号}</div><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_13\" class=\"column\">仓库：</div><div style=\"height: 30px; line-height: 30px; text-align: left; width: 120px;\" id=\"column_14\" class=\"column\">{@仓库}</div><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_15\" class=\"column\">商品总数量：</div><div style=\"height: 30px; line-height: 30px; width: 120px; text-align: left;\" id=\"column_16\" class=\"column\">{@商品总数量}</div></div></div><div type=\"table\" nodel=\"1\" id=\"report_table_body\" class=\"group\" title=\"表格\"><table id=\"table_1\" class=\"table\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr><td style=\"width: 80px;\" class=\"td_title\"><div style=\"width: 80px;\" class=\"td_column\" id=\"column_th_69\">商品名称</div></td><td style=\"width: 80px;\" class=\"td_title\"><div style=\"width: 80px; text-align: left;\" class=\"td_column\" id=\"column_th_70\">商品编码</div></td><td style=\"width: 80px;\" class=\"td_title\"><div style=\"width: 80px; text-align: left;\" class=\"td_column\" id=\"column_th_71\">颜色</div></td><td style=\"width: 80px;\" class=\"td_title\"><div style=\"width: 80px; height: 20px; line-height: 20px;\" class=\"td_column\" id=\"column_th_72\">尺码</div></td><td style=\"width: 80px;\" class=\"td_title\"><div style=\"width: 80px; text-align: right;\" class=\"td_column\" id=\"column_th_73\">条码</div></td><td style=\"width: 80px;\" class=\"td_title\"><div style=\"width: 80px;\" class=\"td_column\" id=\"column_th_134\">数量</div></td><td style=\"width: 80px;\" class=\"td_title\"><div class=\"td_column\" id=\"column_th_141\">库位代码</div></td></tr><!--detail_list--></table></div><div id=\"report_table_bottom\" class=\"group\" title=\"表格尾\"><div style=\"height: 20px;\" nodel=\"1\" id=\"row_2\" class=\"row border\"><div style=\"width: 50px; text-align: eft;\" id=\"column_6\" class=\"column\">合计：</div><div style=\"width: 100px; text-align: right;\" id=\"column_22\" class=\"column\">{@商品总数量}</div></div></div><div id=\"report_bottom\" class=\"group\" title=\"报表尾\"><div style=\"height: 22px;\" nodel=\"1\" id=\"row_3\" class=\"row border\"><div style=\"width: 100px; text-align: left;\" id=\"column_7\" class=\"column\">打印人：</div><div style=\"height: 22px; line-height: 22px; width: 280px; text-align: left;\" id=\"column_54\" class=\"column\">{@打印人}</div><div style=\"height: 22px; line-height: 22px; width: 80px; text-align: center;\" id=\"column_55\" class=\"column\">打印时间：</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_56\" class=\"column\">{@打印时间}</div></div></div></div>' WHERE `print_templates_code`='oms_waves_record_new';",
);

$u['1180']=array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('90010400', '90010000', 'url', '我的服务', 'value/server_info/do_list', '4', '1', '0', '1', '4');",
    "UPDATE sys_action SET ui_entrance=4 WHERE action_id='90000000';",
    "UPDATE sys_action SET ui_entrance=4 WHERE action_id='90010000';",
    "UPDATE sys_action SET ui_entrance=4 WHERE parent_id='90010000';",
);

$u['1102_1'] = array(
	"delete from sys_params where param_code='print_template_choose';",
);
$u['bug_1110'] = array(
    "ALTER TABLE fx_appoint_goods MODIFY COLUMN `fx_rebate` decimal(3,2) NOT NULL DEFAULT '1.00' COMMENT '指定分销商折扣';",
);

$u['1117'] = array(
    "INSERT INTO base_return_reason(return_reason_code,return_reason_name,return_reason_type,is_active,is_sys,remark) VALUES 
        ('TH001','7天无理由退换货',1,1,0,'系统内置'),
        ('TH002','退运费',1,1,0,'系统内置'),
        ('TH003','包装/商品破损/污渍',1,1,0,'系统内置'),
        ('TH004','卖家错发/漏发',1,1,0,'系统内置'),
        ('TH005','商品需要维修',1,1,0,'系统内置'),
        ('TH006','发票问题',1,1,0,'系统内置'),
        ('TH007','大小/尺寸与描述不符',1,1,0,'系统内置'),
        ('TH008','商品质量问题/做工问题',1,1,0,'系统内置'),
        ('TH009','未按约定时间发货',1,1,0,'系统内置'),
        ('TH010','收到假货/假冒品牌',1,1,0,'系统内置'),
        ('TH011','缩水/褪色',1,1,0,'系统内置'),
        ('TH012','颜色/图案/款式与商品描述不符',1,1,0,'系统内置'),
        ('TH013','材质面料与商品描述不符',1,1,0,'系统内置'),
        ('TH014','其他',1,1,0,'系统内置')
    ON DUPLICATE KEY UPDATE return_reason_name=VALUES(return_reason_name);"
);