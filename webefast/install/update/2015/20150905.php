<?php

$u = array();

//增加赠品策略 创建时间字段
$u['FSF-1602'] = array(
	"alter table op_gift_strategy add create_time int(11) not null default '0' comment '创建时间';",
);


//支付宝对账表完善

$u['FSF-1622'] = array(
	"alter table oms_sell_settlement add column account_month date NOT NULL DEFAULT '0000-00-00' COMMENT '财务账期';",
	"alter table oms_sell_settlement add column `check_accounts_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '核销状态 0未对账 10已核销 20部分核销 30虚拟核消  40人工核销 50核销失败';",
	"alter table oms_sell_settlement add column `check_accounts_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '核销时间';",
	"alter table oms_sell_settlement add column `check_accounts_user_code` varchar(20) NOT NULL DEFAULT '' COMMENT '核销人';",
);
//订单下载 商品下载 商品库存同步 网单回写  退单下载  支付宝流水下载  淘宝分销商品下载淘宝分销订单下载定时器增加轮训时间
$u['FSF-1642'] = array(
		"update sys_schedule set loop_time=900 where code='order_download_cmd';",
		"update sys_schedule set loop_time=21600 where code='goods_download_cmd';",
		"update sys_schedule set loop_time=1800 where code='inv_upload_cmd';",
		"update sys_schedule set loop_time=900 where code='logistics_upload';",
		"update sys_schedule set loop_time=900 where code='refund_download_cmd';",
		"update sys_schedule set loop_time=86400 where code='alipay_download_cmd';",
		"update sys_schedule set loop_time=21600 where code='fx_goods_download_cmd';",
		"update sys_schedule set loop_time=900 where code='fx_order_download_cmd';",
		);
//订单查询 问题订单列表 缺货订单列表增加下单时间 付款时间字段
$u['FSF-1628'] = array(
		"delete from sys_user_pref where iid='sell_record_do_list/table' and type='custom_table_field';  ",
		"delete from sys_user_pref where iid='oms/sell_record_question_list' and type='custom_table_field';  ",
		"delete from sys_user_pref where iid='oms/sell_record_short_list' and type='custom_table_field';  ",
		);
$u['FSF-1610'] = array(
		"update sys_action set action_code='sys/flash_templates/edit_td&template_id=31&model=oms/InvoiceRecordModel&typ=default' where action_id=1040800;",
		"update sys_print_templates set template_body='<?xml version=\"1.0\" encoding=\"utf-8\"?><Page product=\"MyReport.TD\" v=\"1.0\"><PageWidth>7.6</PageWidth><PageHeight>12.7</PageHeight><Components><Component type=\"List\" name=\"instance358\" x=\"1\" y=\"91\" width=\"75\" height=\"81\"><Value>=#商品名称</Value><Font size=\"9\"/></Component><Component type=\"List\" name=\"instance655\" x=\"69\" y=\"91\" width=\"36\" height=\"93\"><Value>=#数量</Value><Font size=\"9\"/></Component><Component type=\"List\" name=\"instance804\" x=\"122\" y=\"89\" width=\"39\"><Value>=#均摊金额</Value><Font size=\"9\"/></Component><Component type=\"Label\" name=\"instance1088\" x=\"25\" y=\"29\" height=\"22\"><Value>=@开票抬头</Value></Component><Component type=\"Label\" name=\"instance1297\" x=\"24\" y=\"45\" height=\"22\"><Value>=@开票时间</Value></Component><Component type=\"List\" name=\"instance1410\" x=\"163\" y=\"89\" width=\"50\"><Value>=#均摊金额*#数量</Value><Font size=\"9\"/></Component><Component type=\"Label\" name=\"instance2151\" x=\"38\" y=\"316\" height=\"22\"><Value>=@开票金额大写</Value><Font size=\"9\"/></Component><Component type=\"Label\" name=\"instance2237\" x=\"39\" y=\"291\" height=\"22\"><Value>=@开票金额小写</Value><Font size=\"9\"/></Component></Components></Page>' where print_templates_code='invoice_record'",
		);


//增加批发退货通知单
$u['FSF-1604'] = array(
		"INSERT INTO `sys_action` VALUES ('8020400', '8020000','url','批发退货通知单','wbm/return_notice_record/do_list','2','1','0','1','0');",
	 "INSERT INTO `sys_action` VALUES ('8020401', '8020400', 'act', '确认/取消确认', 'wbm/return_notice_record/do_sure', '2', '1', '0', '1','0');",
	 "INSERT INTO `sys_action` VALUES ('8020402', '8020400', 'act', '删除', 'wbm/return_notice_record/do_delete', '2', '1', '0', '1','0');",
	 "INSERT INTO `sys_action` VALUES ('8020403', '8020400', 'act', '生成退单', 'wbm/return_notice_record/do_return', '2', '1', '0', '1','0');",
	 "INSERT INTO `sys_action` VALUES ('8020404', '8020400', 'act', '完成', 'wbm/return_notice_record/do_finish', '2', '1', '0', '1','0');",
	"CREATE TABLE `wbm_return_notice_record` (
  `return_notice_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `return_notice_code` varchar(64) DEFAULT '' COMMENT '单据编号',
  `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
  `store_code` varchar(128) DEFAULT '' COMMENT '仓库代码',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `init_code` varchar(128) DEFAULT '' COMMENT '原单号',
  `order_time` datetime DEFAULT NULL COMMENT '下单日期',
  `num` int(11) DEFAULT '0' COMMENT '数量(总退货数)',
  `finish_num` int(11) DEFAULT '0' COMMENT '完成数量',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '总金额',
  `return_type_code` varchar(100) DEFAULT '' COMMENT '退货类型代码',
  `is_check` int(4) DEFAULT '0' COMMENT '0未确认 1确认',
  `is_return` int(4) DEFAULT '0' COMMENT '0未生成退单 1生成退单',
  `is_finish` int(4) DEFAULT '0' COMMENT '0未完成 1完成',
  `is_finish_person` varchar(64) DEFAULT '' COMMENT '完成人',
  `is_finish_time` datetime DEFAULT NULL COMMENT '完成时间',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `create_person` varchar(64) DEFAULT '' COMMENT '创建人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`return_notice_record_id`),
  UNIQUE KEY `return_notice_code` (`return_notice_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='批发退货通知单';",
	"CREATE TABLE `wbm_return_notice_detail_record` (
  `return_notice_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `return_notice_record_id` int(11) DEFAULT '0',
  `goods_id` int(11) DEFAULT '0' COMMENT 'goods_id',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `goods_name` varchar(255) DEFAULT '' COMMENT '商品名称',
  `spec1_id` int(11) DEFAULT '0' COMMENT 'color_id',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_id` int(11) DEFAULT '0' COMMENT 'size_id',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `barcode` varchar(128) DEFAULT '' COMMENT '商品条形码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `trade_price` decimal(20,3) DEFAULT '0.000' COMMENT '批发价',
  `sell_price` decimal(20,3) DEFAULT '0.000' COMMENT '标准售价',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `price` decimal(20,3) DEFAULT '0.000' COMMENT '批发单价',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '金额',
  `finish_num` int(11) DEFAULT '0' COMMENT '完成数量',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `return_notice_code` varchar(128) DEFAULT NULL COMMENT '单据编号',
  PRIMARY KEY (`return_notice_record_detail_id`),
  UNIQUE KEY `return_notice_code_barcode` (`return_notice_code`,`barcode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='批发退货通知单明细表';",
"update sys_action set sort_order = 3 where action_code='wbm/return_notice_record/do_list'"
);
