<?php
$u = array();
$u['FSF-1908'] = array(
		"ALTER TABLE api_jingdong_trade_coupon DROP KEY order_id;",
		"ALTER TABLE api_jingdong_trade_coupon ADD KEY `order_id` (`order_id`);",
		);
$u['FSF-1906'] = array(
		"update sys_schedule set loop_time=60 where code='inv_upload_cmd';",
		);
$u['FSF-1902'] = array(
		"ALTER TABLE oms_sell_return MODIFY  `deal_code` varchar(200) NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)';",
		);


$u['FSF-1899'] = array(
		"CREATE TABLE `oms_report_day_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(5) DEFAULT '0',
  `num` int(11) DEFAULT '0',
  `sku` varchar(128) DEFAULT '',
  `record_date` date DEFAULT '0000-00-00',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`type`,`sku`,`record_date`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='零售SKU销售前10';",
		);


$u['FSF-1890'] = array(
		"DELETE from sys_user_pref where iid ='sell_record_do_list/table'",
);
$u['FSF-1904'] = array(
		"ALTER TABLE oms_sell_settlement_record ADD COLUMN  `sell_month` date NOT NULL DEFAULT '0000-00-00' COMMENT '业务账期';",
		"ALTER TABLE oms_sell_settlement_record ADD COLUMN  `sell_month_ym` varchar(7) NOT NULL DEFAULT '0000-00' COMMENT '业务账期--仅年月';",
		"ALTER TABLE oms_sell_settlement_record ADD COLUMN  `account_month` date NOT NULL DEFAULT '0000-00-00' COMMENT '财务账期';",
		"ALTER TABLE oms_sell_settlement_record ADD COLUMN  `account_month_ym` varchar(7) NOT NULL DEFAULT '0000-00' COMMENT '财务账期--仅年月';",
		"ALTER TABLE oms_sell_settlement_record ADD KEY `sell_month` (`sell_month`);",
		"ALTER TABLE oms_sell_settlement_record ADD KEY `sell_month_ym` (`sell_month_ym`)",
		"ALTER TABLE oms_sell_settlement_record ADD KEY `account_month` (`account_month`)",
		"ALTER TABLE oms_sell_settlement_record ADD KEY `account_month_ym` (`account_month_ym`)",
		);
$u['FSF-1896'] = array(
		"ALTER TABLE api_order ADD COLUMN `api_data` text COMMENT '在业务中用到的接口数据以json格式保存'",
                "ALTER TABLE `base_order_label`
                ADD COLUMN `is_sys`  tinyint(1) NULL DEFAULT 0 COMMENT '是否系统内置' AFTER `order_label_img`;",
                "INSERT INTO `base_order_label` VALUES ('1', 'O2O', 'O2O订单', null, '1', '', '2015-12-11 10:11:13');",
    
    
	);	

$u['FSF-1889'] = array(
	"INSERT INTO `base_sale_channel` VALUES ('36', 'xiachufang', 'xcf', '下厨房', '1', '1', '', '2015-12-10 13:57:24');"
);

$u['FSF-1893'] = array("insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
values('','print_delivery_record_template','waves_property','S002_011 发货单打印模版选择','radio','[\"发货单模版\",\"发货单模版新\"]','0','0.00','1-新 0-旧','','发货单模板(新)支持纸张大小设置，请维护发货单模板(新)');",
"INSERT INTO `sys_action` VALUES ('1040110', '1040000', 'url', '发货单模板(新)', 'tprint/tprint/do_edit&print_templates_code=deliver_record', '0', '1', '0', '1','0');",
"INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('deliver_record', '发货单模版', NULL, '20', '0', '0', '0', '210', '297', '无', '{\"conf\":\"deliver_record\",\"page_next_type\":\"0\",\"css\":\"tprint_report\",\"page_size\":\"\"}', '&lt;div id=&quot;report&quot;&gt;&lt;div id=&quot;report_top&quot; class=&quot;group&quot; title=&quot;报表头&quot;&gt;&lt;div style=&quot;height: 50px;&quot; nodel=&quot;1&quot; id=&quot;row_0&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;width: 400px; font-size: 24px; height: 50px; line-height: 50px; text-align: right;&quot; id=&quot;column_0&quot; class=&quot;column&quot;&gt;发货单&lt;/div&gt;&lt;div style=&quot;height: 50px; line-height: 50px;&quot; id=&quot;column_87&quot; class=&quot;column&quot;&gt;&lt;/div&gt;&lt;div style=&quot;height: 50px; line-height: 50px; width: 180px;&quot; id=&quot;column_88&quot; class=&quot;column&quot;&gt;&lt;img src=&quot;assets/tprint/picon/barcode.png&quot; type=&quot;1&quot; class=&quot;barcode&quot; style=&quot;height:50px;width:180px;&quot; title=&quot;{@订单号}&quot;&gt;&lt;/div&gt;&lt;/div&gt;&lt;div style=&quot;height: 30px;&quot; id=&quot;row_4&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_9&quot; class=&quot;column&quot;&gt;交易号：&lt;/div&gt;&lt;div style=&quot;width: 120px; text-align: left; height: 30px; line-height: 30px;&quot; id=&quot;column_12&quot; class=&quot;column&quot;&gt;{@交易号}&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_13&quot; class=&quot;column&quot;&gt;仓库：&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; text-align: left; width: 120px;&quot; id=&quot;column_14&quot; class=&quot;column&quot;&gt;{@发货仓库}&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_15&quot; class=&quot;column&quot;&gt;商品总数量：&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 120px; text-align: left;&quot; id=&quot;column_16&quot; class=&quot;column&quot;&gt;{@商品总数量}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;div type=&quot;table&quot; nodel=&quot;1&quot; id=&quot;report_table_body&quot; class=&quot;group&quot; title=&quot;表格&quot;&gt;&lt;table id=&quot;table_1&quot; class=&quot;table&quot; border=&quot;0&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;&lt;tr&gt;&lt;td style=&quot;width: 130px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 130px;&quot; class=&quot;td_column&quot; id=&quot;column_th_69&quot;&gt;商品名称&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 130px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 130px;&quot; class=&quot;td_column&quot; id=&quot;column_th_70&quot;&gt;商品编码&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 80px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 80px;&quot; class=&quot;td_column&quot; id=&quot;column_th_71&quot;&gt;规格1&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 80px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 80px; height: 20px; line-height: 20px;&quot; class=&quot;td_column&quot; id=&quot;column_th_72&quot;&gt;规格2&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 130px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 130px;&quot; class=&quot;td_column&quot; id=&quot;column_th_73&quot;&gt;条形码&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_th_74&quot;&gt;数量&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 90px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 90px;&quot; class=&quot;td_column&quot; id=&quot;column_th_75&quot;&gt;库位&lt;/div&gt;&lt;/td&gt;&lt;/tr&gt;&lt;!--detail_list--&gt;&lt;/table&gt;&lt;/div&gt;&lt;div id=&quot;report_table_bottom&quot; class=&quot;group&quot; title=&quot;表格尾&quot;&gt;&lt;div nodel=&quot;1&quot; id=&quot;row_2&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;width: 450px; text-align: left;&quot; id=&quot;column_6&quot; class=&quot;column&quot;&gt;合计&lt;/div&gt;&lt;div style=&quot;width: 120px; text-align: left;&quot; id=&quot;column_22&quot; class=&quot;column&quot;&gt;{@支付方式}&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 120px; text-align: left;&quot; id=&quot;column_50&quot; class=&quot;column&quot;&gt;{@商品总数量}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;div id=&quot;report_bottom&quot; class=&quot;group&quot; title=&quot;报表尾&quot;&gt;&lt;div style=&quot;height: 22px;&quot; nodel=&quot;1&quot; id=&quot;row_3&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;width: 100px;&quot; id=&quot;column_7&quot; class=&quot;column&quot;&gt;打印人：&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 280px; text-align: left;&quot; id=&quot;column_54&quot; class=&quot;column&quot;&gt;{@打印人}&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 80px; text-align: center;&quot; id=&quot;column_55&quot; class=&quot;column&quot;&gt;打印时间：&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 120px; text-align: left;&quot; id=&quot;column_56&quot; class=&quot;column&quot;&gt;{@打印时间}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;', '&lt;tr&gt;&lt;td style=&quot;width: 130px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 130px;&quot; class=&quot;td_column&quot; id=&quot;column_td_69&quot;&gt;{#商品名称}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 130px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 130px;&quot; class=&quot;td_column&quot; id=&quot;column_td_70&quot;&gt;{#商品编码}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 80px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 80px;&quot; class=&quot;td_column&quot; id=&quot;column_td_71&quot;&gt;{#规格1}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 80px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 80px;&quot; class=&quot;td_column&quot; id=&quot;column_td_72&quot;&gt;{#规格2}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 130px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 130px;&quot; class=&quot;td_column&quot; id=&quot;column_td_73&quot;&gt;{#条形码}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_td_74&quot;&gt;{#数量}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 90px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 90px;&quot; class=&quot;td_column&quot; id=&quot;column_td_75&quot;&gt;{#库位}&lt;/div&gt;&lt;/td&gt;&lt;/tr&gt;', '');"
);


$u['FSF-1920'] = array(
    "INSERT INTO `sys_action` VALUES ('5020103', '5020100', 'act', '条码编辑', 'prm/goods_barcode/do_edit', '1', '1', '0', '1','0');
",
    "update sys_action set  action_code='prm/goods_barcode/edit_barcode' where action_id='5020103'",
    
    );