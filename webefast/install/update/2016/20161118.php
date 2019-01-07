<?php

$u = array();

$u['817'] = array(
    "UPDATE oms_sell_record_detail od,oms_sell_record o,base_goods g set od.cost_price = g.cost_price WHERE o.is_change_record=1 and o.sell_record_code = od.sell_record_code and od.goods_code=g.goods_code and g.cost_price>0",
    "UPDATE oms_sell_record_detail od,oms_sell_record o,goods_sku g set od.cost_price = g.cost_price WHERE o.is_change_record=1 and o.sell_record_code = od.sell_record_code and od.sku=g.sku and g.cost_price>0"
);

$u['803'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('14020200', '14020000', 'url', '零售单据', 'sys/sap_sell_record/do_list', '1', '1', '0', '1', '1');",
    "CREATE TABLE sap_sell_record
(
	`sap_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`record_code` varchar(128) NOT NULL DEFAULT '' COMMENT '定、退单据编号',
	`order_status` tinyint(11) NOT NULL DEFAULT '0' COMMENT '单据状态.0-未上传；1-已上传',
	`order_type` tinyint(11) NOT NULL DEFAULT '0' COMMENT '单据类型.0-订单；1-退单',
  `deal_code` varchar(64) NOT NULL DEFAULT '' COMMENT '平台交易号',
	`sale_channel_code` varchar(20) NOT NULL DEFAULT '' COMMENT '销售平台',
	`shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
	`store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
	`goods_num` smallint(11) NOT NULL DEFAULT '0' COMMENT '商品数量',
	`order_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单据总额,商品总额+运费+配送手续费',
  `goods_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品总额',
  `express_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
  `delivery_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '配送手续费',
	`upload_info` VARCHAR(128) NOT NULL DEFAULT'' COMMENT '上传失败信息',
	`upload_date` datetime COMMENT '上传时间',
	`record_code_type` varchar(128) NOT NULL DEFAULT '' COMMENT '定、退单据编号+类型',
  `pay_refund_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '支付或退款时间',
  PRIMARY KEY (`sap_record_id`),
  UNIQUE KEY `idxu_key` (`record_code`,`order_type`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='sap订单上传中间表';",
    "CREATE TABLE sap_sell_record_detail
(
	`sap_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_code` varchar(64) NOT NULL DEFAULT '' COMMENT '定、退单据编号',
	`order_type` tinyint(11) NOT NULL DEFAULT '0' COMMENT '单据类型.0-订单；1-退单',
  `deal_code` varchar(64) NOT NULL DEFAULT '' COMMENT '平台交易号',
	`goods_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(20) NOT NULL DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(20) NOT NULL DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(30) NOT NULL DEFAULT '' COMMENT 'sku',
  `barcode` varchar(30) NOT NULL DEFAULT '' COMMENT '条码',
  `goods_price` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '商品单价(实际售价)',
  `cost_price` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '商品成本单价',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
  `avg_money` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '均摊金额',
	`upload_info` VARCHAR(128) NOT NULL DEFAULT'' COMMENT '上传失败信息',
	`record_code_type` varchar(128) NOT NULL DEFAULT '' COMMENT '定、退单据编号+类型',
  PRIMARY KEY (`sap_record_detail_id`),
  UNIQUE KEY `idxu_key1` (`record_code`,`deal_code`,`sku`,`order_type`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='sap订单上传明细中间表';",
    "UPDATE sys_action SET action_name= '集成接口' WHERE action_id ='14020000';",
    "ALTER TABLE sap_adjust_record ADD UNIQUE KEY `idxu_key`(`mjahr`,`matnr`,`shkzg`);",
    "ALTER TABLE sap_adjust_record DROP INDEX idxu_key;  ",
    "ALTER TABLE sap_adjust_record ADD UNIQUE KEY `idxu_key`(`mblnr`,`matnr`,`shkzg`);",
    "ALTER TABLE sap_config ADD COLUMN efast_shop_code varchar(128) DEFAULT '' COMMENT 'efast商店代码';",
    "ALTER TABLE sap_config ADD COLUMN sap_shop_code varchar(128) DEFAULT '' COMMENT 'sap商店代码';",
);
$u['bug_611'] = array(
    "ALTER TABLE stm_store_shift_record ADD COLUMN shift_in_time date DEFAULT NULL COMMENT '(入库)业务时间' AFTER is_shift_in_time;",
    "ALTER TABLE stm_store_shift_record MODIFY COLUMN `record_time` date DEFAULT NULL COMMENT '业务时间(出库)';",
    "UPDATE stm_store_shift_record SET shift_in_time = record_time WHERE is_shift_in = 1 AND shift_in_time IS NULL;",
);

$u['805'] = array(
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('pf_yto', '批发_圆通_普通模板', NULL, '1', '4', '0', '0', '2300', '1270', '无', '{\"detail\":\"detail:goods_name|detail:barcode|detail:num|detail:goods_code|detail:barcode|detail:num|detail:real_weigh|detail:spec1_name|detail:shelf_code|detail:barcode\",\"deteil_row\":\"0\"}', 'LODOP.PRINT_INITA(\"0mm\",\"0mm\",886,500,\"圆通\");\r\nLODOP.SET_PRINT_PAGESIZE(0,2300,1270,\"\");\r\nLODOP.ADD_PRINT_SETUP_BKIMG(\"<img border=\'0\' src=\'http://img04.taobaocdn.com/imgextra/i4/775277144/TB2OEjNaVXXXXX8XXXXXXXXXXXX-775277144.jpg?category=express&id=yto_2013_11.jpg\'/>\");\r\nLODOP.SET_SHOW_MODE(\"BKIMG_LEFT\",1);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_TOP\",1);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_WIDTH\",861);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_HEIGHT\",480);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",218,507,179,21,c[\"receiver_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",103,473,238,26,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_name\",125,470,291,28,c[\"custom_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_phone\",212,152,147,20,c[\"sender_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"print_time\",375,279,125,19,c[\"print_time\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_province\",159,479,72,29,c[\"receiver_province\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_city\",164,580,55,28,c[\"receiver_city\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_district\",165,635,133,24,c[\"receiver_district\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_street\",196,461,310,23,c[\"receiver_street\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"sender\",103,118,106,20,c[\"sender\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_province\",144,114,76,22,c[\"sender_province\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_city\",147,200,45,17,c[\"sender_city\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_district\",152,269,146,22,c[\"sender_district\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_addr\",179,110,287,25,c[\"sender_addr\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_zip\",215,347,53,17,c[\"sender_zip\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_city\",56,652,114,30,c[\"receiver_city\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",16);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"detail:goods_name|detail:barcode|detail:num|detail:goods_code|detail:barcode|detail:num|detail:real_weigh|detail:spec1_name|detail:shelf_code|detail:barcode\",267,122,199,61,\"商品编码  条形码  数量 \");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"order_time\",268,322,150,25,c[\"order_time\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\n', '{\"LODOP.ADD_PRINT_TEXTA(\\\"detail:goods_name|detail:barcode|detail:num|detail:goods_code|detail:barcode|detail:num|detail:real_weigh|detail:spec1_name|detail:shelf_code|detail:barcode\\\",267,122,199,61,\\\"\\u5546\\u54c1\\u7f16\\u7801  \\u6761\\u5f62\\u7801  \\u6570\\u91cf \\\");\":\"var detailstr=\\\"\\\";\\nfor(var i in c[\\\"detail\\\"]){\\nvar detail=c[\\\"detail\\\"][i];\\ndetailstr+=\\\"\\\"+detail[\\\"goods_code\\\"]+\\\"   \\\"+detail[\\\"barcode\\\"]+\\\"   \\\"+detail[\\\"num\\\"]+\\\"  \\\"\\n}\\nLODOP.ADD_PRINT_TEXTA(\\\"detail:goods_name|detail:barcode|detail:num|detail:goods_code|detail:barcode|detail:num|detail:real_weigh|detail:spec1_name|detail:shelf_code|detail:barcode\\\",267,122,199,61,detailstr);\"}', '');",
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('pur_express_print', 'pur', 'S004_002  批发销货单支持快递单打印', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '', '2016-11-15 14:50:42', '默认不开启\r\n开启后，批发销货单详情显示‘打印快递单’按钮，请先往快递单模板中设置模板，然后再点击按钮打印');"
);




$u['835'] = array(
    "ALTER TABLE oms_sell_record_notice ADD COLUMN  `sale_mode` varchar(10) NOT NULL DEFAULT 'stock' COMMENT '销售模式：现货stock，预售presale'",
    "UPDATE oms_sell_record_notice r1,oms_sell_record r2 SET r1.sale_mode = r2.sale_mode WHERE r1.sell_record_code = r2.sell_record_code"
);
$u['bug_687'] = array(
    //外包仓零售单自定义列修复
    "DELETE FROM sys_user_pref WHERE iid='wms/wms_trade';"
);

$u['830']  = array("ALTER TABLE wbm_store_out_record ADD pay_status TINYINT(1) DEFAULT '0' COMMENT '0 未付款 1 已付款' AFTER adjust_money;");
$u['bug_698'] = array(
    "ALTER TABLE oms_sell_record add  KEY `receiver_mobile` (`receiver_mobile`) ;",
	"ALTER TABLE oms_sell_record add  KEY `buyer_name` (`buyer_name`);",
);


$u['810'] = array(
	"CREATE TABLE `crm_goods_children` (
  `crm_goods_id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_code` varchar(50) NOT NULL DEFAULT '' COMMENT '活动代码',
  `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '店铺代码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `p_sku` varchar(128) DEFAULT '' COMMENT '套餐sku',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `inv_num` int(10) NOT NULL COMMENT '获取库存',
	`activity_list` varchar(300) DEFAULT '' COMMENT '参与套餐',
  PRIMARY KEY (`crm_goods_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动子商品表';",
   "ALTER TABLE crm_goods ADD update_num int(10) not null DEFAULT 0  comment'活动上报库存';"
);

$u['810_1'] = array(
	"CREATE TABLE `crm_goods_log` (
	`log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`activity_code` varchar(100) NOT NULL DEFAULT '0' COMMENT '活动代码',
	`user_code` varchar(30) NOT NULL COMMENT '用户代码',
	`user_name` varchar(30) NOT NULL COMMENT '用户名称',
	`action_name` varchar(100) NOT NULL DEFAULT '' COMMENT '操作名称',
	`action_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '操作时间',
	`action_desc` varchar(300) DEFAULT '' COMMENT '操作描述',
	PRIMARY KEY (`log_id`),
	KEY `activity_code` (`activity_code`),
	KEY `action_time` (`action_time`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动商品日志';"
);

$u['808'] = array(
	"CREATE TABLE `payment_type` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `payment_code` varchar(20) NOT NULL COMMENT'支付方式代码',
		`payment_name` varchar(50) NOT NULL COMMENT'支付方式名字',
		`payment_account` varchar(128) NOT NULL COMMENT'账户',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	"CREATE TABLE `payment_account` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `account_name` varchar(20) NOT NULL COMMENT'账户名称',
		`account_bank` varchar(50) NOT NULL COMMENT'开户银行',
		`account_code` varchar(128) NOT NULL COMMENT'银行账号',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);

$u['811'] = array('ALTER TABLE sys_print_templates ADD `template_body_replace_key` text NULL AFTER template_body;');
