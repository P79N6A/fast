<?php

$u['767'] = array(
    //更新JIT箱唛模板名称，箱唛模板改为JIT箱唛模板
    "UPDATE sys_print_templates SET print_templates_name='JIT箱唛模板' WHERE print_templates_code='weipinhuijit_box_print';",
    //增加普通箱唛打印模板
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('general_box_print', '普通箱唛模版', '', '10', '1', '0', '0', '1000', '1010', '无', '', 'LODOP.PRINT_INITA(\"0mm\",\"0mm\",598,863,\"普通箱唛模版\");\r\nLODOP.SET_PRINT_PAGESIZE(0,1000,1010,\"\");\r\nLODOP.ADD_PRINT_LINE(6,3,381,4,0,1);\r\nLODOP.ADD_PRINT_LINE(7,4,8,381,0,1);\r\nLODOP.ADD_PRINT_LINE(8,379,380,380,0,1);\r\nLODOP.ADD_PRINT_LINE(380,2,381,376,0,1);\r\nLODOP.ADD_PRINT_LINE(7,80,381,81,0,1);\r\nLODOP.ADD_PRINT_LINE(53,4,54,381,0,1);\r\nLODOP.ADD_PRINT_LINE(90,4,89,379,0,1);\r\nLODOP.ADD_PRINT_LINE(150,4,151,379,0,1);\r\nLODOP.ADD_PRINT_LINE(215,4,214,379,0,1);\r\nLODOP.ADD_PRINT_LINE(281,4,282,380,0,1);\r\nLODOP.ADD_PRINT_LINE(319,3,320,379,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",60,23,52,25,\"箱序号\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",107,9,66,25,\"批发单号\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",170,9,67,25,\"目的城市\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",234,23,51,25,\"总数量\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",287,26,48,25,\"SKU数\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",19,27,48,25,\"分销商\");\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"box_order\",60,87,150,25,c[\"box_order\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"record_code\",107,88,150,25,c[\"record_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sku_num\",287,86,150,25,c[\"sku_num\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"address\",162,88,223,40,c[\"address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"num\",235,89,150,25,c[\"num\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_name\",19,88,150,25,c[\"custom_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\n', '[]', 'LODOP.PRINT_INITA(\"0mm\",\"0mm\",598,863,\"普通箱唛模版\");\r\nLODOP.SET_PRINT_PAGESIZE(0,1000,1010,\"\");\r\nLODOP.ADD_PRINT_LINE(6,3,381,4,0,1);\r\nLODOP.ADD_PRINT_LINE(7,4,8,381,0,1);\r\nLODOP.ADD_PRINT_LINE(8,379,380,380,0,1);\r\nLODOP.ADD_PRINT_LINE(380,2,381,376,0,1);\r\nLODOP.ADD_PRINT_LINE(7,80,381,81,0,1);\r\nLODOP.ADD_PRINT_LINE(53,4,54,381,0,1);\r\nLODOP.ADD_PRINT_LINE(90,4,89,379,0,1);\r\nLODOP.ADD_PRINT_LINE(150,4,151,379,0,1);\r\nLODOP.ADD_PRINT_LINE(215,4,214,379,0,1);\r\nLODOP.ADD_PRINT_LINE(281,4,282,380,0,1);\r\nLODOP.ADD_PRINT_LINE(319,3,320,379,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",60,23,52,25,\"箱序号\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",107,9,66,25,\"批发单号\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",170,9,67,25,\"目的城市\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",234,23,51,25,\"总数量\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",287,26,48,25,\"SKU数\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",19,27,48,25,\"分销商\");\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"box_order\",60,87,150,25,c[\"box_order\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"record_code\",107,88,150,25,c[\"record_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sku_num\",287,86,150,25,c[\"sku_num\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"address\",162,88,223,40,c[\"address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"num\",235,89,150,25,c[\"num\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_name\",19,88,150,25,c[\"custom_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\n');",
    //装箱汇总单打印模板
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('aggr_box', '装箱汇总单模版', NULL, '20', '0', '0', '0', '210', '297', '无', '{\"conf\":\"aggr_box\",\"page_next_type\":\"0\",\"css\":\"tprint_report\",\"page_size\":\"100\",\"report_top\":\"5\",\"report_left\":\"10\"}', '&lt;div id=&quot;report&quot;&gt;&lt;div id=&quot;report_top&quot; class=&quot;group&quot; title=&quot;报表头&quot;&gt;&lt;div style=&quot;height: 50px;&quot; nodel=&quot;1&quot; id=&quot;row_0&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;width: 718px; font-size: 24px; height: 50px; line-height: 50px; text-align: center;&quot; id=&quot;column_0&quot; class=&quot;column&quot;&gt;装箱汇总单&lt;/div&gt;&lt;/div&gt;&lt;div style=&quot;height: 30px;&quot; id=&quot;row_4&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_9&quot; class=&quot;column&quot;&gt;批发单号：&lt;/div&gt;&lt;div style=&quot;width: 120px; text-align: left; height: 30px; line-height: 30px;&quot; id=&quot;column_12&quot; class=&quot;column&quot;&gt;{@批发单号}&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_13&quot; class=&quot;column&quot;&gt;分销商：&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; text-align: left; width: 120px;&quot; id=&quot;column_14&quot; class=&quot;column&quot;&gt;{@分销商}&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 100px; text-align: right;&quot; id=&quot;column_15&quot; class=&quot;column&quot;&gt;发货仓：&lt;/div&gt;&lt;div style=&quot;height: 30px; line-height: 30px; width: 120px; text-align: left;&quot; id=&quot;column_16&quot; class=&quot;column&quot;&gt;{@发货仓}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;div type=&quot;table&quot; nodel=&quot;1&quot; id=&quot;report_table_body&quot; class=&quot;group&quot; title=&quot;表格&quot;&gt;&lt;table id=&quot;table_1&quot; class=&quot;table&quot; border=&quot;0&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;&lt;tr&gt;&lt;td style=&quot;width: 55px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 55px;&quot; class=&quot;td_column&quot; id=&quot;column_th_69&quot;&gt;箱序号&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 100px;&quot; class=&quot;td_column&quot; id=&quot;column_th_70&quot;&gt;商品品牌&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 100px;&quot; class=&quot;td_column&quot; id=&quot;column_th_71&quot;&gt;商品名称&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 100px; height: 20px; line-height: 20px;&quot; class=&quot;td_column&quot; id=&quot;column_th_72&quot;&gt;商品编码&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 100px;&quot; class=&quot;td_column&quot; id=&quot;column_th_73&quot;&gt;商品条形码&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_th_74&quot;&gt;规格1&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_th_75&quot;&gt;规格2&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 80px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 80px;&quot; class=&quot;td_column&quot; id=&quot;column_th_76&quot;&gt;数量&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 50px;&quot; class=&quot;td_title&quot;&gt;&lt;div style=&quot;width: 50px;&quot; class=&quot;td_column&quot; id=&quot;column_th_77&quot;&gt;金额&lt;/div&gt;&lt;/td&gt;&lt;/tr&gt;&lt;!--detail_list--&gt;&lt;/table&gt;&lt;/div&gt;&lt;div id=&quot;report_table_bottom&quot; class=&quot;group&quot; title=&quot;表格尾&quot;&gt;&lt;div style=&quot;height: 22px;&quot; nodel=&quot;1&quot; id=&quot;row_2&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;width: 250px; text-align: left;&quot; id=&quot;column_6&quot; class=&quot;column&quot;&gt;合计&lt;/div&gt;&lt;div style=&quot;width: 80px; text-align: center;&quot; id=&quot;column_22&quot; class=&quot;column&quot;&gt;SKU总数：&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 120px; text-align: left;&quot; id=&quot;column_50&quot; class=&quot;column&quot;&gt;{@SKU总数}&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 80px; text-align: center;&quot; id=&quot;column_87&quot; class=&quot;column&quot;&gt;数量合计：&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 100px; text-align: left;&quot; id=&quot;column_110&quot; class=&quot;column&quot;&gt;{@总数量}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;div id=&quot;report_bottom&quot; class=&quot;group&quot; title=&quot;报表尾&quot;&gt;&lt;div style=&quot;height: 22px;&quot; nodel=&quot;1&quot; id=&quot;row_3&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;width: 100px;&quot; id=&quot;column_7&quot; class=&quot;column&quot;&gt;打印人：&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 300px; text-align: left;&quot; id=&quot;column_54&quot; class=&quot;column&quot;&gt;{@打印人}&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 80px; text-align: center;&quot; id=&quot;column_55&quot; class=&quot;column&quot;&gt;打印时间：&lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 120px; text-align: left;&quot; id=&quot;column_56&quot; class=&quot;column&quot;&gt;{@打印时间}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;', '&lt;tr&gt;&lt;td style=&quot;width: 55px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 55px;&quot; class=&quot;td_column&quot; id=&quot;column_td_69&quot;&gt;{#箱序号}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 100px;&quot; class=&quot;td_column&quot; id=&quot;column_td_70&quot;&gt;{#商品品牌}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 100px;&quot; class=&quot;td_column&quot; id=&quot;column_td_71&quot;&gt;{#商品名称}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 100px;&quot; class=&quot;td_column&quot; id=&quot;column_td_72&quot;&gt;{#商品编码}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 100px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 100px;&quot; class=&quot;td_column&quot; id=&quot;column_td_73&quot;&gt;{#商品条形码}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_td_74&quot;&gt;{#规格1}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 60px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 60px;&quot; class=&quot;td_column&quot; id=&quot;column_td_75&quot;&gt;{#规格2}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 80px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 80px;&quot; class=&quot;td_column&quot; id=&quot;column_td_76&quot;&gt;{#数量}&lt;/div&gt;&lt;/td&gt;&lt;td style=&quot;width: 50px;&quot; class=&quot;td_detail&quot;&gt;&lt;div style=&quot;width: 50px;&quot; class=&quot;td_column&quot; id=&quot;column_td_77&quot;&gt;{#金额}&lt;/div&gt;&lt;/td&gt;&lt;/tr&gt;', '');"
);


$u['710'] = array(
	"CREATE TABLE `op_api_activity_check_goods` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `check_sn` varchar(128) DEFAULT NULL COMMENT '检查编号',
	  `shop_code` varchar(128) DEFAULT NULL COMMENT '商店代码',
	  `barcode` varchar(128) DEFAULT NULL COMMENT '商品条码',
	  `api_sku_id` varchar(128) DEFAULT NULL COMMENT '平台sku',
	  `api_item_id` varchar(128) DEFAULT NULL COMMENT '平台商品id',
	  `inv_num` int(11) DEFAULT NULL COMMENT '库存数量',
	  `sale_price` decimal(20,2) DEFAULT NULL COMMENT '售价',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `_key` (`check_sn`,`shop_code`,`barcode`) USING BTREE,
	  UNIQUE KEY `_key2` (`check_sn`,`shop_code`,`api_sku_id`) USING BTREE,
	  KEY `_index` (`api_item_id`) USING BTREE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动商品检查表';",
	"CREATE TABLE `op_api_activity_check_process` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `check_sn` varchar(128) DEFAULT NULL COMMENT '检查编号',
	  `shop_code` varchar(128) DEFAULT NULL COMMENT '商店代码',
	  `start_time` int(11) DEFAULT '0' COMMENT '检查开始时间',
	  `end_time` int(11) DEFAULT '0' COMMENT '检查结束时间',
	  `barcode_num` int(11) DEFAULT '0' COMMENT '条码异常数量',
	  `inv_num` int(11) DEFAULT NULL COMMENT '库存异常数量',
	  `sale_price_num` int(11) DEFAULT NULL COMMENT '价格异常数量',
	  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '任务状态',
	  `sys_task_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理任务ID',
            PRIMARY KEY (`id`),
            UNIQUE KEY `_key` (`check_sn`,`shop_code`) USING BTREE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动商品检查任务表';",
	"CREATE TABLE `op_api_activity_check_task` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `shop_code_list` varchar(255) DEFAULT NULL COMMENT '商店代码',
	  `check_data` varchar(128) DEFAULT NULL COMMENT '检查信息',
	  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
	  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '任务状态',
	  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动商品检查任务表';",
	"CREATE TABLE `op_api_activity_goods` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `shop_code` varchar(128) DEFAULT NULL COMMENT '商店代码',
	  `barcode` varchar(128) DEFAULT NULL COMMENT '条码',
	  `sku` varchar(128) DEFAULT NULL,
	  `inv_num` int(11) DEFAULT NULL COMMENT '库存数量',
	  `sale_price` decimal(20,2) DEFAULT NULL COMMENT '售价',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `_key` (`shop_code`,`barcode`) USING BTREE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	"insert into sys_schedule (code,name,status,type,request,path,loop_time,task_module) values('op_api_check_task','商品效验',1,10,'{\"app_act\":\"op\/op_api_activity_check\/check_goods_task\",\"app_fmt\":\"json\"}','webefast/web/index.php',86400,'sys');
	"
);
$u['bug_619'] = array(
    "insert IGNORE into sysdb.sys_action_extend (action_id,extend_code) values ('7020000','efast5_Standard'); ",
);

$u['bug_641'] =array(
    "ALTER TABLE op_gift_strategy_detail MODIFY `ranking_time_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '1:指定时间点 ;0: 循环整点'"
);
$u['bug_623'] = array(
	"update sys_schedule set type=10 where code='auto_goods_init';",
);