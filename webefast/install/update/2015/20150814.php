<?php

$u = array();

$u['FSF-1561'] = array(
    "ALTER TABLE `sys_action`
MODIFY COLUMN `action_code`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '菜单地址' AFTER `action_name`;",
    "ALTER TABLE `sys_print_templates`
ADD COLUMN `print_templates_code`  varchar(128) NULL AFTER `print_templates_id`;",
 "update sys_print_templates set print_templates_code='sf' WHERE print_templates_id=1;",
 "update sys_print_templates set print_templates_code='sfrm2' WHERE print_templates_id=2;",
 "update sys_print_templates set print_templates_code='send_record1' WHERE print_templates_id=3;",
 "update sys_print_templates set print_templates_code='ytorm2' WHERE print_templates_id=4;",
 "update sys_print_templates set print_templates_code='send_record_flash' WHERE print_templates_id=5;",
 "update sys_print_templates set print_templates_code='ztorm' WHERE print_templates_id=6;",
 "update sys_print_templates set print_templates_code='sto' WHERE print_templates_id=7;",
 "update sys_print_templates set print_templates_code='zto' WHERE print_templates_id=8;",
 "update sys_print_templates set print_templates_code='yto' WHERE print_templates_id=9;",
 "update sys_print_templates set print_templates_code='ems' WHERE print_templates_id=10;",
 "update sys_print_templates set print_templates_code='barcode' WHERE print_templates_id=11;",
 "update sys_print_templates set print_templates_code='yunda' WHERE print_templates_id=12;",
 "update sys_print_templates set print_templates_code='yuandarm' WHERE print_templates_id=13;",
 "update sys_print_templates set print_templates_code='postb' WHERE print_templates_id=14;",
 "update sys_print_templates set print_templates_code='wbm_store_out' WHERE print_templates_id=15;",
 "update sys_print_templates set print_templates_code='jd' WHERE print_templates_id=16;",
 "update sys_print_templates set print_templates_code='pur_purchaser' WHERE print_templates_id=17;",
 "update sys_print_templates set print_templates_code='qfkd' WHERE print_templates_id=18;",
 "update sys_print_templates set print_templates_code='htky' WHERE print_templates_id=19;",
 "update sys_print_templates set print_templates_code='box_record' WHERE print_templates_id=20;",
 "update sys_print_templates set print_templates_code='gto' WHERE print_templates_id=22;",
 "update sys_print_templates set print_templates_code='tiantian' WHERE print_templates_id=23;",
 "update sys_print_templates set print_templates_code='sfrm3' WHERE print_templates_id=24;",
 "update sys_print_templates set print_templates_code='emsrm' WHERE print_templates_id=25;",
 "update sys_print_templates set print_templates_code='fast' WHERE print_templates_id=26;",
 "update sys_print_templates set print_templates_code='oms_waves_record' WHERE print_templates_id=27;",
 "update sys_print_templates set print_templates_code='postbrm' WHERE print_templates_id=28;",
 "update sys_print_templates set print_templates_code='pur_return' WHERE print_templates_id=29;",
 "update sys_print_templates set print_templates_code='storm' WHERE print_templates_id=30;",
 "update sys_action set action_name='采购单模板',action_code='sys/flash_templates/edit&template_code=pur_purchaser&model=pur/PurchaseRecordModel&typ=default&tabs=pur' where action_id=1040500",
    
"INSERT IGNORE INTO `sys_print_templates` VALUES ('29', 'pur_return', '采购退货单', '6', '1', '0', '0', '0', '0', '', '{}', '<?xml version=\"1.0\" encoding=\"utf-8\"?><ReportSettings version=\"1.2\"><LeftMargin>1</LeftMargin><RightMargin>1</RightMargin><TopMargin>1.5</TopMargin><BottomMargin>1.5</BottomMargin><PageHeaderRepeat>false</PageHeaderRepeat><PageFooterRepeat>false</PageFooterRepeat><TableHeaderRepeat>true</TableHeaderRepeat><ShowPageNumber>true</ShowPageNumber><PageNumberFormat>【第{0}页 共{1}页】</PageNumberFormat><PageHeaderSettings><ItemSetting type=\"CaptionRowSetting\"><Height>1.3</Height><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>采购退货单</Value><Style><TextAlign>center</TextAlign><FontSize>18</FontSize><FontBold>true</FontBold></Style><Width>14</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting></CaptionCellSettings></ItemSetting><ItemSetting type=\"CaptionRowSetting\"><Height>0.8</Height><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>单据编号：</Value><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@单据编号</Value><Width>3.5</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>原单号：</Value><Width>1.9</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@原单号</Value><Width>3.8</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>下单时间</Value><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@下单时间</Value><Width>5</Width></ItemSetting></CaptionCellSettings></ItemSetting><ItemSetting type=\"CaptionRowSetting\"><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>业务日期：</Value><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@业务日期</Value><Width>3.5</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>供应商：</Value><Width>1.9</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@供应商</Value><Width>3.8</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>仓库：</Value><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@仓库</Value><Width>5</Width></ItemSetting></CaptionCellSettings></ItemSetting><ItemSetting type=\"CaptionRowSetting\"><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>总退货量：</Value><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@总退货数</Value><Width>9.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>总金额：</Value><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@总金额</Value><Width>5</Width></ItemSetting></CaptionCellSettings></ItemSetting></PageHeaderSettings><TableColumnSettings><ItemSetting type=\"TableColumnSetting\"><Width>1.4</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.5</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>1.9</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>1.7</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.5</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>1.4</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>1.8</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.3</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"></ItemSetting></TableColumnSettings><TableHeaderSettings><ItemSetting type=\"TableRowSetting\"><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Value>序号</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>商品名称</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>商品编码</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>规格1</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>规格2</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>商品条形码</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>库位</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>单价</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>退货通知数</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>差异数</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableHeaderSettings><TableDetailSettings><ItemSetting type=\"TableRowSetting\"><Height>0.7</Height><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Value>=#序号</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#商品名称</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#商品编码</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#规格1</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#规格2</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#商品条形码</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#库位</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#单价</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#通知退货数</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#差异数</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableDetailSettings><TableFooterSettings><ItemSetting type=\"TableRowSetting\"><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Value>合计</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=@总退货通知数</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=@总差异数</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableFooterSettings><TableGroupSettings><ItemSetting type=\"TableGroupSetting\"></ItemSetting></TableGroupSettings></ReportSettings>', '', '');",
        
    
);


/**
 * 穿衣助手销售平台
 * by zdd 2015.08.05
 */
$u['FSF-1563'] = array(
		"INSERT INTO `base_sale_channel` VALUES ('31', 'chuanyi', 'cyzs', '穿衣助手', '1', '1', '', '2015-08-05 16:12:24')",
		);
/**
 * 唯品会jit菜单
 * by zdd 2015.08.10
 */
$u['FSF-1547'] = array(
		"INSERT INTO `sys_action` VALUES ('8040000', '8000000', 'group', '唯品会JIT', 'platform-weipinhuijit', '4', '1', '0', '1','0')",
		"INSERT INTO `sys_action` VALUES ('8040100', '8040000', 'url', '档期管理', 'api/api_weipinhuijit_po/do_list', '1', '1', '0', '1','0')",
		"INSERT INTO `sys_action` VALUES ('8040200', '8040000', 'url', '拣货单管理', 'api/api_weipinhuijit_pick/do_list', '2', '1', '0', '1','0')",
		"INSERT INTO `sys_action` VALUES ('8040300', '8040000', 'url', '出库单管理', 'api/api_weipinhuijit_delivery/do_list', '3', '1', '0', '1','0')",
		"CREATE TABLE `api_weipinhuijit_po` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
		  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
		  `notice_id` int(10) DEFAULT '0' COMMENT '绑定的批发通知单id',
		  `notice_record_no` varchar(150) DEFAULT NULL COMMENT '绑定的批发通知单单号',
		  `po_no` varchar(255) NOT NULL COMMENT 'po编号',
		  `co_mode` varchar(255) NOT NULL COMMENT '合作模式编码',
		  `sell_st_time` varchar(50) DEFAULT NULL COMMENT '档期开始销售时间',
		  `sell_et_time` varchar(50) DEFAULT NULL COMMENT '档期结束销售时间',
		  `stock` varchar(50) DEFAULT NULL COMMENT '虚拟总库存',
		  `sales_volume` varchar(50) DEFAULT NULL COMMENT '销售数',
		  `not_pick` varchar(50) DEFAULT NULL COMMENT '未拣货数',
		  `insert_time` varchar(50) NOT NULL COMMENT '插入时间',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `po_no` (`po_no`),
		  KEY `notice_record_no` (`notice_record_no`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8",
		"CREATE TABLE `api_weipinhuijit_pick` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `total` int(11) NOT NULL COMMENT '记录总条数',
		  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
		  `delivery_no` varchar(50) NOT NULL COMMENT '出库单号',
		  `pick_no` varchar(50) NOT NULL COMMENT '拣货单编号',
		  `pick_type` varchar(50) NOT NULL COMMENT '拣货单类别',
		  `notice_record_no` varchar(50) NOT NULL COMMENT '通知单号',
		  `po_no` varchar(50) NOT NULL COMMENT 'PO单编号',
		  `pick_num` int(10) NOT NULL COMMENT '商品拣货数量',
		  `notice_num` int(10) DEFAULT '0' COMMENT '通知数',
		  `delivery_num` int(10) DEFAULT '0' COMMENT '发货数',
		  `sell_st_time` int(10) NOT NULL COMMENT '档期开始销售时间',
		  `sell_et_time` int(10) NOT NULL COMMENT '档期结束销售时间',
		  `export_time` int(10) NOT NULL COMMENT '导出时间',
		  `export_num` int(11) NOT NULL COMMENT '导出次数',
		  `warehouse` varchar(50) NOT NULL COMMENT '送货仓库',
		  `order_cate` varchar(50) NOT NULL COMMENT '订单类别',
		  `delivery_id` int(11) DEFAULT NULL COMMENT '出库单外键ID',
		  `insert_time` varchar(50) DEFAULT NULL COMMENT '插入时间',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `pick_no` (`pick_no`),
		  KEY `po_no` (`po_no`),
		
		  KEY `delivery_no` (`delivery_no`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8",
		"CREATE TABLE `api_weipinhuijit_pick_goods` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `pick_no` varchar(150) NOT NULL COMMENT '拣货单编号',
		  `po_no` varchar(150) NOT NULL COMMENT 'PO单编号',
		  `stock` int(10) NOT NULL COMMENT '商品拣货数量',
		  `notice_stock` int(10) DEFAULT '0' COMMENT '通知数',
		  `delivery_stock` int(10) DEFAULT '0' COMMENT '发货数',
		  `barcode` varchar(150) NOT NULL COMMENT '商品条码',
		  `sku` varchar(150) NOT NULL COMMENT '系统sku',
		  `art_no` varchar(150) NOT NULL COMMENT '货号',
		  `product_name` varchar(150) NOT NULL COMMENT '商品名称',
		  `size` varchar(150) NOT NULL COMMENT '尺码',
		  `actual_unit_price` varchar(10) DEFAULT NULL COMMENT '供货价（不含税）',
		  `actual_market_price` varchar(10) DEFAULT NULL COMMENT '供货价（含税）',
		  PRIMARY KEY (`id`),
		  KEY `pick_no` (`pick_no`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_weipinhuijit_store_out_record` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
		
		  `delivery_no` varchar(50) NOT NULL COMMENT '出库单号',
		  `notice_record_no` varchar(50) NOT NULL COMMENT '通知单号',
		  `pick_no` varchar(50) NOT NULL COMMENT '拣货单编号',
		
		  `store_out_record_no` varchar(50) NOT NULL COMMENT '批发销货单号',
		  `po_no` varchar(50) NOT NULL COMMENT 'PO单编号',
		  `warehouse` varchar(50) NOT NULL COMMENT '送货仓库',
		  `delivery_id` varchar(50) DEFAULT NULL COMMENT '出库单Id',
		  `storage_no` varchar(50) DEFAULT NULL COMMENT '入库编号',
		 
		  `insert_time` varchar(50) DEFAULT NULL COMMENT '插入时间',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `store_out_record_no` (`store_out_record_no`),
		  KEY `po_no` (`po_no`),
		  KEY `notice_record_no` (`notice_record_no`),
		  KEY `pick_no` (`pick_no`)
		
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_weipinhuijit_delivery` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `shop_code` varchar(100) DEFAULT NULL COMMENT '店铺code',
		  `po_no` varchar(50) DEFAULT NULL COMMENT 'po号',
		  `delivery_no` varchar(50) DEFAULT NULL COMMENT '送货单编号',
		  `warehouse` varchar(50) DEFAULT NULL COMMENT '送货仓库',
		  `arrival_time` varchar(50) DEFAULT NULL COMMENT '预计到货时间',
		  `express_code` varchar(50) DEFAULT NULL COMMENT '配送方式code',
		  `carrier_name` varchar(50) DEFAULT NULL COMMENT '承运商名称',
		  `driver_tel` varchar(50) DEFAULT NULL COMMENT '司机联系电话',
		  `delivery_id` varchar(50) DEFAULT NULL COMMENT '出库单Id',
		  `storage_no` varchar(50) DEFAULT NULL COMMENT '入库编号',
		  `amount` int(10) DEFAULT NULL COMMENT '商品数量',
		  `insert_time` varchar(50) DEFAULT NULL COMMENT '插入时间',
		  `is_delivery` int(1) NOT NULL DEFAULT '0' COMMENT '是否确认出库(0:未出库；1:已出库)',
		  `delivery_time` datetime DEFAULT NULL COMMENT '确认出库时间',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `delivery_id` (`delivery_id`),
		  KEY `delivery_time` (`delivery_time`),
		  KEY `insert_time` (`insert_time`),
		  KEY `is_delivery` (`is_delivery`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_weipinhuijit_delivery_detail` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `pid` int(11) NOT NULL,
			  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
			  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
			  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
			  `sku` varchar(255) DEFAULT NULL COMMENT 'sku',
			  `barcode` varchar(255) DEFAULT NULL COMMENT '条形码',
			  `record_code` varchar(100) DEFAULT NULL COMMENT '批发销货单号',
			  `box_no` varchar(100) DEFAULT NULL COMMENT '供应商箱号',
			  `pick_no` varchar(100) DEFAULT NULL COMMENT '拣货单号',
			  `amount` int(10) DEFAULT NULL COMMENT '商品数量',
			  `vendor_type` varchar(100) DEFAULT NULL COMMENT '供应商类型： COMMON：普通 3pl：3PL',
			  PRIMARY KEY (`id`),
			  KEY `record_code` (`record_code`),
			  KEY `pick_no` (`pick_no`),
			  KEY `goods_code` (`goods_code`),
			  KEY `barcode` (`barcode`),
			  KEY `sku` (`sku`),
			  KEY `pid` (`pid`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;",
		"alter table wbm_store_out_record add column  `enotice_num` int(11) DEFAULT '0' COMMENT '通知数量'",
		);
	/**
	 * 美丽说销售平台
	 * by zdd 2015.08.11
	 */
	$u['FSF-1574'] = array(
			"INSERT INTO `base_sale_channel` VALUES ('32', 'meilishuo', 'mls', '美丽说', '1', '1', '', '2015-08-11 16:12:24')",
	);
	/**
	 * api_jingdong_trade_printdata_detail唯一键更改
	 * by zdd 2015.08.12
	 */
	$u['FSF-1582'] = array(
			"alter table api_jingdong_trade_printdata_detail drop key order_id;",
			"alter table api_jingdong_trade_printdata_detail add UNIQUE KEY `order_id` (`id`,`ware`);",
			);
        
	$u['FSF-1558'] = array(
			"INSERT INTO `sys_print_templates` (`print_templates_id`, `print_templates_name`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES (31, '邮政小包_电子面单_二联', 1, 1, 0, 0, 1000, 1500, '无', '{\"detail\":\"detail:goods_name|detail:spec1_name|detail:spec2_name|detail:num\",\"deteil_row\":\"1\"}', 'LODOP.PRINT_INITA(\"0mm\",\"0mm\",400,600,\"圆通_电子面单1423534791403\");\r\nLODOP.SET_PRINT_PAGESIZE(0,1000,1500,\"\");\r\nLODOP.ADD_PRINT_SETUP_BKIMG(\"<img border=\'0\' src=\'/www/webroot/efast/webefast/uploads/邮政.jpg\' />\");\r\nLODOP.SET_SHOW_MODE(\"BKIMG_LEFT\",1);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_TOP\",1);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_WIDTH\",30);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_HEIGHT\",32);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:寄件人：\",215,15,65,16,\"寄件人：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:收件人：\",124,14,85,20,\"收件人：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",11);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:收件人/代收人：\",271,41,115,16,\"收件人/代收人：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:寄件人：\",490,16,69,16,\"寄件人：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:收件人：\",396,13,75,16,\"收件人：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(119,6,372,1,2,0);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-3ea\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(266,8,367,1,2,0);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-44f\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(544,9,371,1,2,0);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-454\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender\",215,74,82,16,c[\"sender\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_mobile\",215,152,104,16,c[\"sender_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",124,88,101,20,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",11);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_province\",144,14,96,20,c[\"receiver_province\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",11);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_city\",145,113,87,20,c[\"receiver_city\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",11);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.ADD_PRINT_TEXTA(\"sender\",489,83,78,16,c[\"sender\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_mobile\",489,164,180,16,c[\"sender_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",396,81,101,16,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_province\",413,16,90,16,c[\"receiver_province\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_city\",412,108,91,16,c[\"receiver_city\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no\",64,91,297,45,\"128A\",c[\"express_no\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:签收时间：\",271,178,115,16,\"签收时间：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:年  月   日\",294,250,94,16,\"年  月   日\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(213,11,359,1,2,0);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-44e\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(484,14,363,1,2,0);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-451\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_address\",232,16,357,32,c[\"sender_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",124,189,138,20,c[\"receiver_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",11);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_district\",145,203,136,20,c[\"receiver_district\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",11);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_addr\",166,14,354,45,c[\"receiver_addr\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",11);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_address\",510,15,353,32,c[\"sender_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",396,181,172,16,c[\"receiver_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_addr\",430,14,355,36,c[\"receiver_addr\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_district\",413,201,160,16,c[\"receiver_district\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no\",348,11,249,40,\"128A\",c[\"express_no\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_RECT(268,170,1,49,2,0);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-7d2\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_phone\",215,227,150,16,c[\"sender_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_city\",5,13,170,52,c[\"receiver_city\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",25);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_district\",5,181,206,52,c[\"receiver_district\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",25);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",466,19,83,20,\"订单号：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sell_record_code\",466,107,203,20,c[\"sell_record_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_phone\",124,239,150,20,c[\"receiver_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",550,16,234,22,\"收寄局：罗湖电商    收寄人：陈德华\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",321,53,76,20,\"重量： 500g\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(58,4,59,381,0,1);\r\nLODOP.ADD_PRINT_LINE(393,2,394,376,0,1);\r\n', '[]', '');
",
			);
	$u['FSF-1583'] = array(
			"insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
				values('','order_tag','oms_taobao','订单标签','radio','[\"关闭\",\"开启\"]','0','0.00','1-开启 0-关闭','2015-08-13 13:17:36','开启后，下载订单时查询订单标签并下载到系统中');",
			"CREATE TABLE `api_order_tag` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `tag_id` varchar(100) DEFAULT NULL COMMENT '订单标签记录id',
				  `source` varchar(30) NOT NULL COMMENT '销售平台',
				  `shop_code` varchar(255) NOT NULL COMMENT '店铺代码',
				  `tid` varchar(20) NOT NULL COMMENT '交易号',
				  `tag_type` tinyint(2) NOT NULL,
				  `gmt_modified` datetime DEFAULT NULL COMMENT '平台中记录的最新修改时间',
				  `gmt_created` datetime DEFAULT NULL,
				  `tag_name` varchar(100) NOT NULL COMMENT '标签名称',
				  `tag_value` text NOT NULL COMMENT '标签值，json格式',
				  `visible` tinyint(1) DEFAULT NULL COMMENT '该标签在消费者端是否显示,0:不显示,1：显示',
				  `insert_time` datetime DEFAULT NULL COMMENT '插入时间',
				  PRIMARY KEY (`id`),
				  KEY `tid` (`tid`),
				  KEY `tag_type` (`tag_type`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单标签表';",
			);
