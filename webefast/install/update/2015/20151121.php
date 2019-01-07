<?php

/* 
 * 双十一期间开发功能主线不合并分支功能SQL文件
 */


$u['FSF-1795'] = array(
	"DROP TABLE IF EXISTS `unique_code_scan_temporary_log`;",
	"CREATE TABLE `unique_code_scan_temporary_log` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`sell_record_code` varchar(64) DEFAULT '' COMMENT '扫描订单号',
		`unique_code` varchar(64) DEFAULT '',
		`barcode_type` varchar(64) DEFAULT '' COMMENT '条码类型,barcode:条形码;unique_code:唯一码;child_barcode:子条码;',
		`lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='商品唯一码跟踪日志临时表';"

);
$u['FSF-1789'] = array(
	"INSERT INTO `sys_user_pref` (`user_id`, `type`, `iid`, `content`) VALUES ('1', 'custom_table_field', 'oms/sell_record_td_list', '[{\"type\":\"button\",\"show\":1,\"title\":\"\\u64cd\\u4f5c\",\"field\":\"_operate\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":0},{\"type\":\"text\",\"show\":1,\"title\":\"\\u65e5\\u5fd7\",\"field\":\"change_remark\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":1},{\"type\":\"text\",\"show\":1,\"title\":\"\\u9500\\u552e\\u5e73\\u53f0\",\"field\":\"source\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":2},{\"type\":\"text\",\"show\":1,\"title\":\"\\u5e97\\u94fa\",\"field\":\"shop_code_name\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":3},{\"type\":\"text\",\"show\":1,\"title\":\"\\u4e0b\\u5355\\u65f6\\u95f4\",\"field\":\"order_first_insert_time\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":4},{\"type\":\"text\",\"show\":1,\"title\":\"\\u4ed8\\u6b3e\\u65f6\\u95f4\",\"field\":\"pay_time\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":5},{\"type\":\"text\",\"show\":1,\"title\":\"\\u4ea4\\u6613\\u53f7\",\"field\":\"tid\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":6},{\"type\":\"text\",\"show\":1,\"title\":\"\\u5141\\u8bb8\\u8f6c\\u5355\",\"field\":\"status\",\"width\":\"80\",\"align\":\"\",\"format_js\":{\"type\":\"map_checked\"},\"rules\":[],\"sort\":7},{\"type\":\"text\",\"show\":1,\"title\":\"\\u4e70\\u5bb6\\u5907\\u6ce8\",\"field\":\"buyer_remark\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":8},{\"type\":\"text\",\"show\":1,\"title\":\"\\u5356\\u5bb6\\u5907\\u6ce8\",\"field\":\"seller_remark\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":9},{\"type\":\"text\",\"show\":1,\"title\":\"\\u4e70\\u5bb6\\u6635\\u79f0\",\"field\":\"buyer_nick\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":10},{\"type\":\"text\",\"show\":1,\"title\":\"\\u6570\\u91cf\",\"field\":\"num\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":11},{\"type\":\"text\",\"show\":1,\"title\":\"\\u91d1\\u989d\",\"field\":\"order_money\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":12},{\"type\":\"text\",\"show\":1,\"title\":\"\\u6536\\u8d27\\u4eba\",\"field\":\"receiver_name\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":13},{\"type\":\"text\",\"show\":1,\"title\":\"\\u5e73\\u53f0\\u6807\\u7b7e\",\"field\":\"tag_name\",\"width\":\"150\",\"align\":\"\",\"rules\":[],\"sort\":14},{\"type\":\"text\",\"show\":1,\"title\":\"\\u8f6c\\u5355\\u72b6\\u6001\",\"field\":\"is_change\",\"width\":\"100\",\"align\":\"\",\"format_js\":{\"type\":\"map\",\"value\":{\"1\":\"\\u5df2\\u8f6c\\u5355\",\"0\":\"\\u672a\\u8f6c\\u5355\",\"-1\":\"\\u672a\\u8f6c\\u5355\"}},\"rules\":[],\"sort\":15},{\"type\":\"text\",\"show\":1,\"title\":\"\\u7cfb\\u7edf\\u8ba2\\u5355\\u53f7\",\"field\":\"sell_record_code\",\"width\":\"100\",\"align\":\"\",\"rules\":[],\"sort\":16}]');"
);
$u['FSF-1805'] = array(
		"ALTER TABLE b2b_box_record ADD COLUMN `is_print` tinyint(3) DEFAULT '0' COMMENT '是否打印装箱单';",
		"insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
		values('','auto_print_box','waves_property','S002_007 自动打印装箱单','radio','[\"关闭\",\"开启\"]','0','0.00','1-开启 0-关闭','','开启后，装箱扫描完成，点击‘下一箱’时会自动打印装箱单，需提前在装箱单模板中配置默认打印机');",
		"insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
		values('','auto_print_jit_box','waves_property','S002_008 自动打印JIT箱唛','radio','[\"关闭\",\"开启\"]','0','0.00','1-开启 0-关闭','','开启后，装箱扫描完成，点击‘下一箱’时会自动打印箱唛单，需提前在箱唛模板中配置默认打印机');",
);
$u['FSF-1807'] = array(
		"ALTER TABLE oms_sell_settlement ADD COLUMN `real_point_fee` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '天猫积分抵扣金额';",
);


$u['FSF-1822'] = array(
    "ALTER TABLE `stm_profit_loss_lof`
ADD COLUMN `record_code_list`  text NULL AFTER `order_code`;
",
    "ALTER TABLE `op_policy_express_area`
DROP INDEX `_key` ,
ADD UNIQUE INDEX `_key` (`area_id`) USING BTREE ;
",
 "ALTER TABLE `op_policy_express_area`
ADD INDEX `_index1` (`pid`) USING BTREE ;
",
   //增加字段长度 
"ALTER TABLE `oms_deliver_record`
MODIFY COLUMN `deal_code`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '交易号' AFTER `sell_record_code`;
",
"ALTER TABLE `oms_sell_record`
MODIFY COLUMN `deal_code`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)' AFTER `sell_record_code`;
", 


    
);
$u['FSF-1804'] = array(
		"CREATE TABLE `api_zouxiu_trade` (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `shop_code` varchar(200) NOT NULL  COMMENT '店铺code',
		  `orderId` varchar(32) NOT NULL DEFAULT '0' COMMENT '走秀官网订单号',
		  `packageId` varchar(32) NOT NULL DEFAULT '0' COMMENT '走秀包裹ID(EBS生成的包裹号)',
		  `packageNumber` varchar(32) DEFAULT '' COMMENT '走秀包裹号',
		  `deliveryNo` varchar(32) DEFAULT '' COMMENT '运单号',
		  `deliveryName` varchar(32) DEFAULT '' COMMENT '快递公司名称',
		  `shippingTime` varchar(64) DEFAULT '' COMMENT '配送时间',
		  `shippingRemark` varchar(250) DEFAULT '' COMMENT '配送备注',
		  `status` int(1) DEFAULT '1' COMMENT '订单状态(0:待备货,1:待发货,2:已发货,3:拒收订单,4:退货订单,5:超期未发货订单)',
		  `createTime` varchar(32) DEFAULT '' COMMENT '创建时间 yyyy-MM-dd HH:mm:ss',
		  `totalFee` decimal(15,2) DEFAULT '0.00' COMMENT '包裹金额(商品单价*数量)，单位元',
		  `payMethod` int(1) DEFAULT '0' COMMENT '是否货到付款0:非COD 1:COD',
		  `codFee` decimal(15,2) DEFAULT '0.00' COMMENT '包裹金额(COD金额，单位元)',
		  `postFee` decimal(15,2) DEFAULT '0.00' COMMENT '运费',
		  `logisticsStatus` varchar(64) DEFAULT '' COMMENT '发货状态',
		  `shipOrderUrl` varchar(300) DEFAULT '' COMMENT '发货单图片url',
		  `eWaybillUrl` varchar(300) DEFAULT '' COMMENT '运单图片url',
		  `receiverName` varchar(30) DEFAULT '' COMMENT '收货人姓名',
		  `receiverState` varchar(64) DEFAULT '' COMMENT '收货人所在省份',
		  `receiverCity` varchar(64) DEFAULT '' COMMENT '收货人所在城市',
		  `receiverDistrict` varchar(255) DEFAULT '' COMMENT '收货人所在区县',
		  `receiverAddress` varchar(255) DEFAULT '' COMMENT '收货人详细地址',
		  `receiverZip` varchar(20) DEFAULT '' COMMENT '收货人邮编',
		  `receiverMobile` varchar(20) DEFAULT '' COMMENT '收货人手机号码',
		  `receiverPhone` varchar(20) DEFAULT '' COMMENT '收货人固定电话',
		  `buyerRemark` varchar(255) DEFAULT '' COMMENT '买家备注',
		  `isInvoice` varchar(2) DEFAULT '0' COMMENT '是否需要发票0:否 1:是',
		  `invoiceName` varchar(255) DEFAULT '' COMMENT '发票抬头',
		  `invoiceContent` varchar(255) DEFAULT '' COMMENT '发票内容',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `INDEX_TRADEID` (`packageId`),
		  KEY `shop_code` (`shop_code`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		);
$u['FSF-1818'] = array(
	"delete from sys_user_pref where iid = 'oms/sell_record_combine_ex_list'",
	"delete from sys_user_pref where iid = 'sell_record_do_list/table'"
);
$u['FSF-1808'] = array(
		"ALTER TABLE oms_sell_settlement ADD COLUMN `record_time` datetime NOT NULL COMMENT '下单时间';",
		"ALTER TABLE oms_sell_settlement ADD COLUMN `pay_time` datetime NOT NULL COMMENT '支付时间';",
		"ALTER TABLE oms_sell_settlement ADD KEY `pay_time` (`pay_time`);",
		"ALTER TABLE oms_sell_settlement ADD KEY `record_time` (`record_time`);",
		"ALTER TABLE oms_sell_settlement ADD KEY `sell_month` (`sell_month`);",
		"ALTER TABLE oms_sell_settlement ADD KEY `sell_month_ym` (`sell_month_ym`);",
		"ALTER TABLE oms_sell_settlement ADD KEY `account_month` (`account_month`);",
		"ALTER TABLE oms_sell_settlement ADD KEY `account_month_ym` (`account_month_ym`);",
		"ALTER TABLE oms_sell_settlement ADD KEY `check_accounts_time` (`check_accounts_time`);",
		);
$u['FSF-1829'] = array(
		"CREATE TABLE `api_chuchujie_trade` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `order_id` varchar(50) NOT NULL COMMENT '订单id',
		  `order_url` varchar(250) NOT NULL,
		  `status_text` varchar(100) DEFAULT '' COMMENT '订单状态文本说明',
		  `total_price` varchar(20) DEFAULT '0.00' COMMENT '订单总金额',
		  `ctime` varchar(20) DEFAULT '' COMMENT '订单的创建时间',
		  `comment` varchar(255) DEFAULT '' COMMENT '买家留言',
		  `express_price` varchar(20) DEFAULT '0.00' COMMENT '运费',
		  `express_id` varchar(20) DEFAULT '' COMMENT '快递号',
		  `express_company` varchar(20) DEFAULT '' COMMENT '快递公司',
		  `pay_time` varchar(20) DEFAULT '' COMMENT '付款时间',
		  `send_time` varchar(20) DEFAULT '' COMMENT '发货时间',
		  `last_status_time` varchar(20) DEFAULT '' COMMENT '订单关闭时间',
		  `seller_note` varchar(255) DEFAULT '' COMMENT '商家备注',
		  `postcode` varchar(50) DEFAULT NULL COMMENT '邮编',
		  `nickname` varchar(100) DEFAULT NULL COMMENT '收件人',
		  `phone` varchar(50) DEFAULT NULL COMMENT '收货人手机号',
		  `address` varchar(255) DEFAULT NULL COMMENT '收货人地址',
		  `province` varchar(50) DEFAULT NULL COMMENT '省',
		  `city` varchar(50) DEFAULT NULL COMMENT '市',
		  `district` varchar(50) DEFAULT NULL COMMENT '区',
		  `street` varchar(100) DEFAULT NULL COMMENT '街道',
		  `shop_code` varchar(50) DEFAULT '' COMMENT '商店代码',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `order_id` (`order_id`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;",
		"CREATE TABLE `api_chuchujie_order` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `order_id` varchar(50) NOT NULL COMMENT '订单id',
		  `goods_id` varchar(250) NOT NULL COMMENT '商品id',
		  `goods_title` varchar(255) DEFAULT '' COMMENT '商品标题',
		  `goods_img` varchar(255) DEFAULT '0.00' COMMENT '商品图片url',
		  `price` varchar(20) DEFAULT '0.00' COMMENT '单价',
		  `amount` varchar(20) DEFAULT '0' COMMENT '数量',
		  `goods_no` varchar(100) DEFAULT '' COMMENT '货号',
		  `outer_id` varchar(255) DEFAULT '' COMMENT '商品sku编码',
		  `short_title` varchar(100) DEFAULT '' COMMENT '标题简写',
		  `refund_status_text` varchar(20) DEFAULT '' COMMENT '退货状态',
		  `shop_code` varchar(50) DEFAULT '' COMMENT '商店代码',
		  `prop` varchar(255) DEFAULT '' COMMENT '规格',
		  PRIMARY KEY (`id`),
		  KEY `order_id` (`order_id`) USING BTREE
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;",
		);
$u['FSF-1848'] = array(
    "update sys_action set action_code='tprint/tprint/do_edit&print_templates_code=b2b_box' where action_id=1040600",
    "
INSERT INTO sys_print_templates
(  print_templates_code,  print_templates_name,  company_code,  type,  is_buildin,  offset_top,  offset_left,  paper_width,  paper_height,  printer,  template_val,  template_body,  template_body_replace, template_body_default)
VALUES ('b2b_box', '装箱单', null, '20', '0', '0', '0', '210', '297', '无', '{\"conf\":\"b2b_box\",\"page_next_type\":\"0\",\"css\":\"tprint_report\"}', '&lt;div id=&quot;report&quot;&gt;&lt;div id=&quot;report_top&quot; class=&quot;group&quot; title=&quot;报表头&quot;&gt;&lt;div style=&quot;height: 50px;&quot; nodel=&quot;1&quot; id=&quot;row_0&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;width: 400px; font-size: 24px; height: 50px; line-height: 50px; text-align: right;&quot; id=&quot;column_0&quot; class=&quot;column&quot;&gt;装箱单&lt;/div&gt;&lt;div style=&quot;height: 50px; line-height: 50px;&quot; id=&quot;column_87&quot; class=&quot;column&quot;&gt;&lt;/div&gt;&lt;div style=&quot;height: 50px; line-height: 50px; width: 180px;&quot; id=&quot;column_88&quot; class=&quot;column&quot;&gt;&lt;img src=&quot;assets/tprint/picon/barcode.png&quot; type=&quot;1&quot; class=&quot;barcode&quot; style=&quot;height:50px;width:180px;&quot; title=&quot;{@装箱任务号}&quot;&gt;&lt;/div&gt;&lt;/div&gt;&lt;div style=&quot;height: 30px;&quot; id=&quot;row_4&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_9&quot; class=&quot;column&quot;&gt;箱号：&lt;/div&gt;&lt;div style=&quot;width: 120px; text-align: left; height: 30px; line-height: 30px;&quot; id=&quot;column_12&quot; class=&quot;column&quot;&gt;{@箱号}&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_13&quot; class=&quot;column&quot;&gt;关联单号：&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; text-align: left; width: 120px;&quot; id=&quot;column_14&quot; class=&quot;column&quot;&gt;{@关联单号}&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_15&quot; class=&quot;column&quot;&gt;关联单据类型：&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 120px; text-align: left;&quot; id=&quot;column_16&quot; class=&quot;column&quot;&gt;{@关联单类型}&lt;/div&gt;&lt;/div&gt;&lt;div style=&quot;height: 30px;&quot; id=&quot;row_5&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_10&quot; class=&quot;column&quot;&gt;仓库：&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 120px; text-align: left;&quot; id=&quot;column_17&quot; class=&quot;column&quot;&gt;{@打印时间}&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_18&quot; class=&quot;column&quot;&gt;总数量：&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 120px; text-align: left;&quot; id=&quot;column_19&quot; class=&quot;column&quot;&gt;{@总数量}&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_20&quot; class=&quot;column&quot;&gt;总金额：&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 120px; text-align: left;&quot; id=&quot;column_21&quot; class=&quot;column&quot;&gt;{@总金额}&lt;/div&gt;&lt;/div&gt;&lt;div id=&quot;row_8&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 100px; text-align: right;&quot; id=&quot;column_38&quot; class=&quot;column&quot;&gt;扫描人：&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 120px; text-align: left;&quot; id=&quot;column_39&quot; class=&quot;column&quot;&gt;{@扫描人}&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 100px; text-align: right;&quot; id=&quot;column_40&quot; class=&quot;column&quot;&gt;装箱时间：&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 120px; text-align: left;&quot; id=&quot;column_41&quot; class=&quot;column&quot;&gt;{@装箱时间}&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 100px; text-align: right;&quot; id=&quot;column_42&quot; class=&quot;column&quot;&gt;&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 120px; text-align: left;&quot; id=&quot;column_43&quot; class=&quot;column&quot;&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;div type=&quot;table&quot; nodel=&quot;1&quot; id=&quot;report_table_body&quot; class=&quot;group&quot; title=&quot;表格&quot;&gt;&lt;table id=&quot;table_1&quot; class=&quot;table&quot; border=&quot;0&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;&lt;tr&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_th_69&quot;&gt;序号&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 120px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 120px;&quot; class=&quot;td_column&quot; id=&quot;column_th_70&quot;&gt;商品名称&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 100px;&quot; class=&quot;td_column&quot; id=&quot;column_th_71&quot;&gt;商品编码&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 60px; height: 20px; line-height: 20px;&quot; class=&quot;td_column&quot; id=&quot;column_th_72&quot;&gt;规格1&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_th_73&quot;&gt;规格2&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_th_74&quot;&gt;单价&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_th_75&quot;&gt;数量&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 80px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 80px;&quot; class=&quot;td_column&quot; id=&quot;column_th_76&quot;&gt;金额&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 100px;&quot; class=&quot;td_column&quot; id=&quot;column_th_77&quot;&gt;库位&lt;/div&gt;&lt;/td&gt;&lt;/tr&gt;&lt;!--detail_list--&gt;&lt;/table&gt;&lt;/div&gt;&lt;div id=&quot;report_table_bottom&quot; class=&quot;group&quot; title=&quot;表格尾&quot;&gt;&lt;div nodel=&quot;1&quot; id=&quot;row_2&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;width: 450px; text-align: left;&quot; id=&quot;column_6&quot; class=&quot;column&quot;&gt;合计&lt;/div&gt;&lt;div style=&quot;width: 120px; text-align: left;&quot; id=&quot;column_22&quot; class=&quot;column&quot;&gt;{@总金额}&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 120px; text-align: left;&quot; id=&quot;column_50&quot; class=&quot;column&quot;&gt;{@总数量}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;div id=&quot;report_bottom&quot; class=&quot;group&quot; title=&quot;报表尾&quot;&gt;&lt;div style=&quot;height: 22px;&quot; nodel=&quot;1&quot; id=&quot;row_3&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;width: 100px;&quot; id=&quot;column_7&quot; class=&quot;column&quot;&gt;打印人：&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 280px; text-align: left;&quot; id=&quot;column_54&quot; class=&quot;column&quot;&gt;{@打印人}&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 80px; text-align: center;&quot; id=&quot;column_55&quot; class=&quot;column&quot;&gt;打印时间：&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 120px; text-align: left;&quot; id=&quot;column_56&quot; class=&quot;column&quot;&gt;{@打印时间}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;', '&lt;tr&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_td_69&quot;&gt;{#序号}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 120px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 120px;&quot; class=&quot;td_column&quot; id=&quot;column_td_70&quot;&gt;{#商品名称}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 100px;&quot; class=&quot;td_column&quot; id=&quot;column_td_71&quot;&gt;{#商品编码}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_td_72&quot;&gt;{#规格1}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_td_73&quot;&gt;{#规格2}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_td_74&quot;&gt;{#单价}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_td_75&quot;&gt;{#数量}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 80px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 80px;&quot; class=&quot;td_column&quot; id=&quot;column_td_76&quot;&gt;{#金额}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 100px;&quot; class=&quot;td_column&quot; id=&quot;column_td_77&quot;&gt;{#库位}&lt;/div&gt;&lt;/td&gt;&lt;/tr&gt;', '');
",
    
);
$u['FSF-1846'] = array(
		"insert into sys_params(param_code,parent_code,param_name,type,form_desc,`value`,remark,memo) values('no_sync_bili','app','S008_001    库存同步服务，熔断百分比设置','text','','30','%','本次同步库存的商品中，0库存商品条码数超过总数的百分比，为防止库存数据异常导致商品下架。本次库存同步被终止。');",
		);