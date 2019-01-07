<?php

$u = array();
$u['FSF-1958'] = array(
    "alter table op_policy_express add store_code varchar(255) DEFAULT '' COMMENT '仓库';",
    "alter table base_shop add express_data varchar(255) DEFAULT '' COMMENT '快递公司';",
);
$u['FSF-1959'] = array(
    "alter table base_goods add period_validity varchar(50) DEFAULT 0 comment '有效期'",
    "alter table base_goods add operating_cycles varchar(50) DEFAULT 0 comment '使用周期'",
);

$u['FSF-1960'] = array(
    "alter table oms_sell_record drop column order_label_code;",
);

$u['FSF-1962'] = array(
    "insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
		values('','direct_cancel','app','S008_003 直接作废订单操作生成退款类型售后服务单','radio','[\"关闭\",\"开启\"]','0','10','1-开启 0-关闭','','开启后，直接作废订单操作，系统会自动生成一张退款类型的售后服务单');",
    "insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
		values('','delivery_create_return','app','S008_004 订单发货，金额存在已付>应付，生成退款类型售后服务单','radio','[\"关闭\",\"开启\"]','0','10','1-开启 0-关闭','','开启后，订单发货成功，订单金额已付款>应付款，系统会自动生成一张退款类型的售后服务单');"
);
$u['FSF-1963'] = array(
    "INSERT INTO `sys_action` VALUES ('3020700', '3020000', 'url', '订单赠品策略', 'op/gift_strategy/do_list', '4', '1', '0', '1','0');",
    "ALTER TABLE op_gift_strategy_detail MODIFY  `give_way` tinyint(3) NOT NULL DEFAULT '0' COMMENT '赠送方式0固定送赠品，1随机送赠品';",
    "ALTER TABLE op_gift_strategy_detail ADD COLUMN `sort` int(7) DEFAULT NULL COMMENT '序号';",
	"ALTER TABLE op_gift_strategy_detail MODIFY `sort` VARCHAR(10) DEFAULT NULL COMMENT '序号'",
    "ALTER TABLE op_gift_strategy_detail ADD COLUMN `name` varchar(255) DEFAULT NULL COMMENT '规则名称';",
    "ALTER TABLE op_gift_strategy_detail ADD COLUMN `level` tinyint(3) DEFAULT NULL COMMENT '优先级';",
    "ALTER TABLE op_gift_strategy_detail ADD COLUMN `time_type` tinyint(3) DEFAULT '0' COMMENT '时间维度 0:付款时间 1：下单时间';",
    "ALTER TABLE op_gift_strategy_detail ADD COLUMN `range_type` tinyint(3) DEFAULT '0' COMMENT '金额/活动商品范围设置0：手工 1：倍增';",
    "ALTER TABLE op_gift_strategy_detail ADD COLUMN `doubled` varchar(20) DEFAULT NULL COMMENT '倍增值';",
    "ALTER TABLE op_gift_strategy_detail ADD COLUMN `status` tinyint(3) DEFAULT '0' COMMENT '0：停用 1：启用';",
    "ALTER TABLE op_gift_strategy_detail MODIFY `goods_condition` tinyint(3) NOT NULL DEFAULT '1' COMMENT '商品条件0固定商品条件，1随机商品条件2全场买送';",
    "ALTER TABLE op_gift_strategy_goods ADD COLUMN `op_gift_strategy_range_id` int(11) NOT NULL DEFAULT '0' COMMENT '金额/数量范围id';",
    "ALTER TABLE op_gift_strategy_goods ADD COLUMN `is_combo` tinyint(3) DEFAULT '0' COMMENT '0普通商品1套餐';",
    "ALTER TABLE op_gift_strategy_customer ADD COLUMN `op_gift_strategy_detail_id` int(11) NOT NULL DEFAULT '0' COMMENT '明细ID';",
    "ALTER TABLE op_gift_strategy_customer DROP KEY _index_key;",
    "ALTER TABLE op_gift_strategy_customer ADD UNIQUE KEY `_index_key` (`op_gift_strategy_detail_id`,`buyer_name`);",
    "ALTER TABLE op_gift_strategy ADD COLUMN `time_type` tinyint(3) DEFAULT '0' COMMENT '时间维度 0:付款时间 1：下单时间';",
    "ALTER TABLE op_gift_strategy ADD COLUMN `combine_upshift` tinyint(3) DEFAULT '0' COMMENT '合并订单赠品升档 0:否 1：是';",
    "ALTER TABLE op_gift_strategy_goods DROP KEY _index_key",
    "ALTER TABLE op_gift_strategy_goods ADD UNIQUE KEY `_index_key` (`op_gift_strategy_detail_id`,`sku`,`is_gift`,`op_gift_strategy_range_id`);",
    "ALTER TABLE goods_sku ADD UNIQUE KEY `barcode` (`barcode`);",
    "CREATE TABLE `op_gift_strategy_range` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `op_gift_strategy_detail_id` int(11) DEFAULT NULL COMMENT '规则id',
			  `range_start` varchar(100) DEFAULT NULL COMMENT '开始范围',
			  `range_end` varchar(100) DEFAULT NULL COMMENT '结束范围',
			  `give_way` tinyint(3) NOT NULL DEFAULT '0' COMMENT '赠送方式0固定送赠品，1随机送赠品',
			  `gift_num` int(11) NOT NULL DEFAULT '1' COMMENT '随机赠送数量',
			  PRIMARY KEY (`id`),
			  KEY `op_gift_strategy_detail_id` (`op_gift_strategy_detail_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;",
    "CREATE TABLE `op_gift_strategy_shop` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `strategy_code` varchar(128) NOT NULL DEFAULT '' COMMENT '策略代码',
			  `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '店铺代码',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `strategy_code_shop` (`strategy_code`,`shop_code`),
			  KEY `strategy_code` (`strategy_code`),
			  KEY `shop_code` (`shop_code`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
);
$u['FSF-1964'] = array(
    "ALTER TABLE api_order ADD COLUMN `type` varchar(30) DEFAULT NULL COMMENT '交易类型';",
);

$u['FSF-1966'] = array(
    "INSERT INTO `base_question_label`(`question_label_code`, `question_label_name`, `is_active`, `is_sys`, `remark`, `lastchanged`) VALUES('EXCEPTION_ADDRESS','异常地址',0,1,'平台订单转单时，买家收货地址不全或者包含特殊字符，订单将自动设问',now())",
    "ALTER TABLE `base_question_label` ADD COLUMN `content`  text NULL AFTER `is_sys`",
);
$u['FSF-1965'] = array("INSERT INTO `sys_action` VALUES ('6020400', '6020000', 'url', '商品批次库存查询', 'prm/inv_lof/do_list', '1', '1', '0', '0','0');");

$u['FSF-1974'] = array(
    "ALTER TABLE `oms_waves_record` DEFAULT CHARACTER SET utf8 ,
modify `record_code` varchar(128) CHARACTER SET utf8 NOT NULL COMMENT '单据编号',
modify `store_code` varchar(128) CHARACTER SET utf8 NOT NULL COMMENT '仓库代码',
modify `express_code` varchar(128) CHARACTER SET utf8 NOT NULL COMMENT '快递公司',
modify `picker` varchar(128) CHARACTER SET utf8 NOT NULL COMMENT '拣货员',
modify `accept_user` varchar(128) CHARACTER SET utf8 NOT NULL COMMENT '验收操作人',
modify `cancel_user` varchar(128) CHARACTER SET utf8 NOT NULL COMMENT '取消操作人';",
);
$u['FSF-1968'] = array(
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('dangdang_print', '当当货到付款模板', '', '1', '1', '0', '0', '980', '1500', '无', '{\"detail\":\"detail:goods_name|detail:spec1_name|detail:spec2_name|detail:num\",\"deteil_row\":\"1\"}', 'LODOP.PRINT_INITA(\"0mm\",\"0mm\",372,540,\"当当货到付款\");\r\nLODOP.SET_PRINT_PAGESIZE(0,980,1500,\"\");\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no\",92,30,192,47,\"128C\",c[\"express_no\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ShowBarText\",0);\r\nLODOP.ADD_PRINT_BARCODEA(\"deal_code\",397,8,221,40,\"128C\",c[\"deal_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ShowBarText\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_province\",14,8,85,25,c[\"receiver_province\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_city\",15,110,134,25,c[\"receiver_city\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_district\",45,75,190,30,c[\"receiver_district\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_IMAGE(0,295,\"18.92mm\",\"14.76mm\",\"URL:assets/images/dangdang.gif\");\r\nLODOP.SET_PRINT_STYLEA(0,\"Stretch\",2);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",110,292,68,25,\"宅急送\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",177,3,59,25,\"收件人：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"Alignment\",3);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",178,73,120,25,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(80,-13,79,368,0,1);\r\nLODOP.ADD_PRINT_LINE(171,-4,172,368,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",214,4,68,25,\"收货地址：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"Alignment\",3);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_address\",206,87,173,36,c[\"sender_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",246,5,46,25,\"电话：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_mobile\",247,69,150,25,c[\"sender_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",277,7,90,23,\"品名：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",302,4,93,25,\"件数：1\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",302,102,83,25,\"重量：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",331,5,165,27,\"签收人：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",358,96,147,30,\"签收时间：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(173,262,535,263,0,1);\r\nLODOP.ADD_PRINT_LINE(204,0,205,260,2,1);\r\nLODOP.ADD_PRINT_LINE(243,-36,244,368,2,1);\r\nLODOP.ADD_PRINT_LINE(275,-2,276,258,0,1);\r\nLODOP.ADD_PRINT_LINE(328,-9,329,368,0,1);\r\nLODOP.ADD_PRINT_LINE(389,-10,390,367,2,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",184,276,77,25,\"送货要求：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sendGoodsTime\",214,264,105,25,c[\"sendGoodsTime\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",250,269,70,25,\"代收货款：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"payable_money\",294,268,90,25,c[\"payable_money\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"buyer_remark\",336,264,103,47,c[\"buyer_remark\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",5);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",144,51,77,25,\"快递公司运单号：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"微软雅黑\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"express_no\",144,130,77,25,c[\"express_no\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"微软雅黑\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",393,266,89,25,\"商家编号：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",473,4,69,25,\"商家名称：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"微软雅黑\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",447,17,98,25,\"当当网订单号：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"微软雅黑\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",5);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"deal_code\",447,119,129,25,c[\"deal_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"微软雅黑\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",5);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",499,4,69,25,\"寄件地址：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"微软雅黑\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",470,267,94,25,\"商家联系电话：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"shopID\",424,266,99,35,c[\"shopID\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_mobile\",499,266,100,35,c[\"sender_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_address\",500,74,185,36,c[\"sender_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"微软雅黑\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",60,294,66,18,\"平台COD\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(1,-9,2,368,0,1);\r\nLODOP.ADD_PRINT_LINE(537,-12,538,367,0,1);\r\nLODOP.ADD_PRINT_LINE(1,1,536,2,0,1);\r\nLODOP.ADD_PRINT_LINE(1,370,537,371,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"shopName\",474,73,186,25,c[\"shopName\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"微软雅黑\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\n', '[]', 'LODOP.PRINT_INITA(\"0mm\",\"0mm\",500,597,\"顺丰_电子面单_二联\");\r\nLODOP.SET_PRINT_PAGESIZE(0,1000,1500,\"\");\r\nLODOP.ADD_PRINT_RECT(7,9,363,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-129\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(8,9,1,327,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-139\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"寄方:\",51,14,33,15,\"寄方:\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"收方：\",97,13,39,15,\"收方：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_shop_name\",51,41,102,15,c[\"sender_shop_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_address\",72,13,196,15,c[\"sender_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"sender\",96,47,49,15,c[\"sender\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"托寄物数量\",237,15,60,15,\"托寄物数量\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"实际重量\",239,91,50,15,\"实际重量\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"付款方式:\",282,15,70,15,\"付款方式:\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"保价声明值：\",297,15,84,15,\"保价声明值：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"代收货款金额：\",311,14,95,15,\"代收货款金额：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"收件：\",440,18,38,13,\"收件：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"托寄物\",496,17,205,15,\"托寄物               SKU         ...(省略)\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"Arial\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"原寄地\",49,276,46,15,\"原寄地\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"目的地\",91,270,44,15,\"目的地\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(141,9,362,3,0,2);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-1719\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(255,9,365,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-7f43b\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(335,9,363,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-3509\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(45,10,362,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-5010\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(89,10,362,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-10410\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(8,371,1,327,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-13371\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_address\",118,14,200,15,c[\"receiver_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"codAmount-1bd449\",310,98,62,15,\"代收货款：$[data]\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",440,53,81,13,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_address\",464,14,205,15,c[\"receiver_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_phone\",50,148,111,13,c[\"sender_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"Arial\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_tel\",58,148,110,12,c[\"sender_tel\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"Arial\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_zip\",73,210,55,15,c[\"sender_zip\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_phone\",96,172,86,15,c[\"receiver_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",97,102,74,15,c[\"receiver_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"Arial\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no-cc\",156,21,229,68,\"128C\",\"2001000\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no-cc\",362,33,200,60,\"128C\",\"2001000\");\r\nLODOP.ADD_PRINT_TEXTA(\"detail:goods_name|detail:spec1_name|detail:spec1_name|detail:num\",517,16,215,40,\"商品名称 规格1 规格2 数量\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"微软雅黑\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"_ic-2a6f1b\",-12,221,60,45,\"E\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"Arial Black\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",40);\r\nLODOP.SET_PRINT_STYLEA(0,\"Alignment\",2);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"电商特惠\",147,271,96,26,\"电商特惠\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",16);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"收件员\",179,272,78,15,\"收件员\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"寄件日期\",198,271,74,15,\"寄件日期\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"派件员\",216,272,57,15,\"派件员\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"运费\",238,247,30,15,\"运费\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"费用合计\",238,305,50,15,\"费用合计\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"计费重量\",239,170,50,15,\"计费重量\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"第三方地区：\",278,255,79,15,\"第三方地区：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"月结账号：\",286,171,74,15,\"月结账号：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"保费：\",301,173,50,15,\"保费：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"收方签署:\",301,261,65,15,\"收方签署:\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"Italic\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"卡号：\",316,173,49,15,\"卡号：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"日期： 月  日  时\",317,262,98,15,\"日期： 月  日  时\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"Italic\",1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"寄件：\",352,242,41,15,\"寄件：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"订单号：\",440,245,65,18,\"订单号：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"备注\",496,283,34,15,\"备注\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"Arial\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(232,12,358,3,0,2);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-2809\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(235,81,1,39,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-81c9c\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(272,10,362,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-81c1c\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(349,10,363,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-3749\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(350,10,1,208,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-3759\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(434,10,360,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-4299\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(44,269,1,187,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-174279\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(494,11,359,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-52910\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(513,11,363,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-1bdb29\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(558,10,363,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-5639\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"c_region_code-49228\",104,271,96,30,\"大头笔：$[data]\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"Arial\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",26);\r\nLODOP.SET_PRINT_STYLEA(0,\"Alignment\",2);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_zip_code\",120,213,55,15,c[\"receiver_zip_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",352,282,66,15,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_phone\",365,243,106,15,c[\"sender_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_mobile\",379,244,107,15,c[\"sender_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_address\",392,241,126,25,c[\"sender_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_zip\",419,274,86,15,c[\"sender_zip\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_phone\",439,136,88,12,c[\"receiver_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",451,137,85,13,c[\"receiver_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"record_code\",460,241,125,19,c[\"record_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"Arial\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_zip_code\",479,175,52,15,c[\"receiver_zip_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"buyer_remark\",522,246,121,28,c[\"buyer_remark\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"Arial\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_RECT(177,269,100,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-1bcfd4\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(195,271,100,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-1bcfd6\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(214,269,101,1,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-1bcfd7\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(231,293,1,40,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-81c99\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(233,149,1,38,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-81c9b\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(234,230,1,38,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-81c9a\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(350,239,1,143,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-375220\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(350,372,1,209,0,1);\r\nLODOP.SET_PRINT_STYLEA(0,\"ItemName\",\"_ic-375371\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);');",
);

$u['FSF-196711'] = array(
    "CREATE TABLE `wms_b2b_order_lof` (
	  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
	  `record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '订单号',
	  `record_type` varchar(20) NOT NULL DEFAULT '' COMMENT '订单类型',
	  `barcode` varchar(20) NOT NULL DEFAULT '' COMMENT '商品条码',
	  `lof_no` varchar(128) NOT NULL DEFAULT '',
	  `production_date` date NOT NULL DEFAULT '0000-00-00',
	  `efast_sl` int(11) NOT NULL DEFAULT '-1' COMMENT 'efast商品数量',
	  `wms_sl` int(11) NOT NULL DEFAULT '-1' COMMENT 'wms商品数量',
	  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `idx_record_code_type` (`record_code`,`record_type`,`barcode`,`lof_no`,`production_date`) USING BTREE,
	  KEY `barcode` (`barcode`) USING BTREE,
	  KEY `record_code` (`record_code`) USING BTREE,
	  KEY `record_type` (`record_type`) USING BTREE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    "CREATE TABLE `wms_oms_order_lof` (
	  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
	  `record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '订单号',
	  `record_type` varchar(20) NOT NULL DEFAULT '' COMMENT '订单类型',
	  `barcode` varchar(20) NOT NULL DEFAULT '' COMMENT '商品条码',
	  `lof_no` varchar(128) NOT NULL DEFAULT '',
	  `production_date` date NOT NULL DEFAULT '0000-00-00',
	  `efast_sl` int(11) NOT NULL DEFAULT '-1' COMMENT 'efast商品数量',
	  `wms_sl` int(11) NOT NULL DEFAULT '-1' COMMENT 'wms商品数量',
	  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `idx_record_code_type` (`record_code`,`record_type`,`barcode`,`lof_no`,`production_date`) USING BTREE,
	  KEY `barcode` (`barcode`) USING BTREE,
	  KEY `record_code` (`record_code`) USING BTREE,
	  KEY `record_type` (`record_type`) USING BTREE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
);

$u['FSF-1978'] = array(
    "UPDATE `sys_schedule` set `loop_time`='300' WHERE `id`='13'",
);

$u['FSF-1982'] = array(
		"alter table wms_oms_trade add cancel_flag tinyint (1) not null default 0 COMMENT '1取消，0未取消';",
		"alter table wms_b2b_trade add cancel_flag tinyint (1) not null default 0 COMMENT '1取消，0未取消';",
		"alter table wms_oms_trade add new_record_code VARCHAR (20) not null default '' COMMENT '新单号';",
		"alter table wms_b2b_trade add new_record_code VARCHAR (20) not null default '' COMMENT '新单号';"
);
$u['FSF-1969'] = array(
		"ALTER TABLE api_order ADD COLUMN `buyer_alipay_no` varchar(50) DEFAULT '' COMMENT '买家支付宝账号';",
		"ALTER TABLE oms_sell_record ADD COLUMN `buyer_alipay_no` varchar(50) DEFAULT '' COMMENT '买家支付宝账号';",
		"ALTER TABLE oms_deliver_record ADD COLUMN `buyer_alipay_no` varchar(50) DEFAULT '' COMMENT '买家支付宝账号';",
		"ALTER TABLE oms_sell_return ADD COLUMN `buyer_alipay_no` varchar(50) DEFAULT '' COMMENT '买家支付宝账号';",
		"delete from sys_user_pref where iid = 'sell_return_finance/do_list' and type='custom_table_field'",
		"ALTER TABLE oms_sell_record ADD COLUMN `order_sign_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '订单签收状态.0-未签收；1-已签收';",
);
$u['FSF-1983'] = array(
		"ALTER TABLE api_goods_sku ADD COLUMN `spec1_name` varchar(100) DEFAULT '' COMMENT '规格1'",
		"ALTER TABLE api_goods_sku ADD COLUMN `spec2_name` varchar(100) DEFAULT '' COMMENT '规格2'",
		"ALTER TABLE api_goods_sku ADD COLUMN `sale_mode` varchar(10) NOT NULL DEFAULT 'stock' COMMENT '销售模式：现货stock，预售presale'",
		"ALTER TABLE api_order ADD COLUMN `sale_mode` varchar(10) NOT NULL DEFAULT 'stock' COMMENT '销售模式：现货stock，预售presale'",
		"ALTER TABLE api_order_detail ADD COLUMN `sale_mode` varchar(10) NOT NULL DEFAULT 'stock' COMMENT '销售模式：现货stock，预售presale'",
		"ALTER TABLE oms_sell_record ADD COLUMN `sale_mode` varchar(10) NOT NULL DEFAULT 'stock' COMMENT '销售模式：现货stock，预售presale'",
		);



$u['FSF-19700'] = array(
    "ALTER TABLE `goods_shelf`
ADD UNIQUE INDEX `_key` (`sku`, `batch_number`, `store_code`, `shelf_code`) USING BTREE ;
",
);

$u['FSF-19710'] = array(
    "ALTER TABLE `oms_waves_strategy`
ADD COLUMN `type`  smallint(5) NULL DEFAULT 0 COMMENT '0单SKU，1包含某个SKU' AFTER `user_code`;
",
);

$u['FSF-1985'] = array(
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('store_shift', '移仓单模版', NULL, '20', '0', '0', '0', '210', '297', '无', '{\"conf\":\"store_shift\",\"page_next_type\":\"0\",\"css\":\"tprint_report\",\"page_size\":\"\"}', '<div id=\"report\"><div id=\"report_top\" class=\"group\" title=\"报表头\"><div style=\"height: 50px;\" nodel=\"1\" id=\"row_0\" class=\"row border\"><div style=\"width: 400px; font-size: 24px; height: 50px; line-height: 50px; text-align: right;\" id=\"column_0\" class=\"column\">移仓单</div><div style=\"height: 50px; line-height: 50px;\" id=\"column_87\" class=\"column\"></div><div style=\"height: 50px; line-height: 50px; width: 180px;\" id=\"column_88\" class=\"column\"><img src=\"assets/tprint/picon/barcode.png\" type=\"1\" class=\"barcode\" style=\"height:50px;width:180px;\" title=\"{@单据编号}\"></div></div><div style=\"height: 30px;\" id=\"row_4\" class=\"row border\"><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_9\" class=\"column\">单据编号：</div><div style=\"width: 120px; text-align: left; height: 30px; line-height: 30px;\" id=\"column_12\" class=\"column\">{@单据编号}</div><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_13\" class=\"column\">移出仓库：</div><div style=\"height: 30px; line-height: 30px; text-align: left; width: 120px;\" id=\"column_14\" class=\"column\">{@移出仓库}</div><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_15\" class=\"column\">移入仓库：</div><div style=\"height: 30px; line-height: 30px; width: 120px; text-align: left;\" id=\"column_16\" class=\"column\">{@移入仓库}</div></div></div><div type=\"table\" nodel=\"1\" id=\"report_table_body\" class=\"group\" title=\"表格\"><table id=\"table_1\" class=\"table\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td style=\"width: 130px;\" class=\"td_title\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_th_69\">商品名称</div></td><td style=\"width: 130px;\" class=\"td_title\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_th_70\">商品编码</div></td><td style=\"width: 80px;\" class=\"td_title\"><div style=\"width: 80px;\" class=\"td_column\" id=\"column_th_71\">规格1</div></td><td style=\"width: 80px;\" class=\"td_title\"><div style=\"width: 80px; height: 20px; line-height: 20px;\" class=\"td_column\" id=\"column_th_72\">规格2</div></td><td style=\"width: 130px;\" class=\"td_title\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_th_73\">条形码</div></td><td style=\"width: 70px;\" class=\"td_title\"><div style=\"width: 70px;\" class=\"td_column\" id=\"column_th_74\">移出数量</div></td><td style=\"width: 90px;\" class=\"td_title\"><div style=\"width: 90px;\" class=\"td_column\" id=\"column_th_75\">库位</div></td></tr><!--detail_list--></table></div><div id=\"report_table_bottom\" class=\"group\" title=\"表格尾\"><div nodel=\"1\" id=\"row_2\" class=\"row border\"><div style=\"width: 450px; text-align: left;\" id=\"column_6\" class=\"column\">合计</div><div style=\"width: 120px; text-align: left;\" id=\"column_22\" class=\"column\">{@商品总数量}</div></div></div><div id=\"report_bottom\" class=\"group\" title=\"报表尾\"><div style=\"height: 22px;\" nodel=\"1\" id=\"row_3\" class=\"row border\"><div style=\"width: 100px;\" id=\"column_7\" class=\"column\">打印人：</div><div style=\"height: 22px; line-height: 22px; width: 280px; text-align: left;\" id=\"column_54\" class=\"column\">{@打印人}</div><div style=\"height: 22px; line-height: 22px; width: 80px; text-align: center;\" id=\"column_55\" class=\"column\">打印时间：</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_56\" class=\"column\">{@打印时间}</div></div></div></div>', '<tr><td style=\"width: 130px;\" class=\"td_detail\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_td_69\">{#商品名称}</div></td><td style=\"width: 130px;\" class=\"td_detail\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_td_70\">{#商品编码}</div></td><td style=\"width: 80px;\" class=\"td_detail\"><div style=\"width: 80px;\" class=\"td_column\" id=\"column_td_71\">{#规格1}</div></td><td style=\"width: 80px;\" class=\"td_detail\"><div style=\"width: 80px;\" class=\"td_column\" id=\"column_td_72\">{#规格2}</div></td><td style=\"width: 130px;\" class=\"td_detail\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_td_73\">{#条形码}</div></td><td style=\"width: 70px;\" class=\"td_detail\"><div style=\"width: 70px;\" class=\"td_column\" id=\"column_td_74\">{#移出数量}</div></td><td style=\"width: 90px;\" class=\"td_detail\"><div style=\"width: 90px;\" class=\"td_column\" id=\"column_td_75\">{#库位}</div></td></tr>', '');",
    "INSERT INTO `sys_action` VALUES ('1041000', '1040000', 'url', '移仓单模版', 'tprint/tprint/do_edit&print_templates_code=store_shift', '0', '1', '0', '1','0');",
    "alter table op_policy_express modify column store_code text;"
);


$u['FSF-1986'] = array(
    "ALTER TABLE `oms_sell_record_detail`
    ADD COLUMN `combo_sku`  varchar(128) NULL DEFAULT '' AFTER `sku`;"
);


$u['FSF-198611'] = array(
    "ALTER TABLE `oms_sell_record_notice`
ADD COLUMN `sku_all`  varchar(256) DEFAULT ''  AFTER `fenxiao_name`,
ADD INDEX `sku_all` (`sku_all`) USING BTREE ;"
);


$u['FSF-1992'] = array(
    "update sys_print_templates set print_templates_name = '批发销货单模版' where print_templates_code = 'wbm_store_out';",
    "update sys_print_templates set print_templates_name = '波次单模版' where print_templates_code = 'oms_waves_record';",
    "update sys_print_templates set print_templates_name = '采购入库单模版' where print_templates_code = 'pur_purchaser';",
    "update sys_print_templates set print_templates_name = '采购退货单模版' where print_templates_code = 'pur_return';",
    "update sys_print_templates set print_templates_name = '装箱单模版' where print_templates_code = 'b2b_box';",
    "update sys_print_templates set print_templates_name = '发货单模版' where print_templates_code = 'send_record_flash';",
    "update sys_print_templates set print_templates_name = '发货单模版(新)',type=30 where print_templates_code = 'deliver_record';",
    "update sys_print_templates set type = 40 where print_templates_code = 'store_shift';",
    "DELETE from sys_action where action_code in ('sys/flash_templates/edit&template_id=5&model=oms/DeliverRecordModel&typ=default',
        'tprint/tprint/do_edit&print_templates_code=deliver_record',
        'sys/flash_templates/edit_td&template_id=11&model=prm/GoodsBarcodeModel&typ=default',
        'sys/flash_templates/edit&template_id=15&model=wbm/StoreOutRecordModel&typ=default',
        'sys/flash_templates/edit&template_code=pur_purchaser&model=pur/PurchaseRecordModel&typ=default&tabs=pur',
        'tprint/tprint/do_edit&print_templates_code=b2b_box',
        'sys/flash_templates/edit&template_id=27&model=oms/WavesRecordModel&typ=default',
        'sys/flash_templates/edit_td&template_id=31&model=oms/InvoiceRecordModel&typ=default',
        'sys/weipinhuijit_box_print/do_list',
        'tprint/tprint/do_edit&print_templates_code=store_shift')",
    "INSERT INTO `sys_action` VALUES ('1041100', '1040000', 'url', '单据模板', 'sys/record_templates/do_list', '10', '1', '0', '1','0');",
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('store_shift', '移仓单模版', NULL, '40', '0', '0', '0', '210', '297', '无', '{\"conf\":\"store_shift\",\"page_next_type\":\"0\",\"css\":\"tprint_report\",\"page_size\":\"\"}', '<div id=\"report\"><div id=\"report_top\" class=\"group\" title=\"报表头\"><div style=\"height: 50px;\" nodel=\"1\" id=\"row_0\" class=\"row border\"><div style=\"width: 400px; font-size: 24px; height: 50px; line-height: 50px; text-align: right;\" id=\"column_0\" class=\"column\">移仓单</div><div style=\"height: 50px; line-height: 50px;\" id=\"column_87\" class=\"column\"></div><div style=\"height: 50px; line-height: 50px; width: 180px;\" id=\"column_88\" class=\"column\"><img src=\"assets/tprint/picon/barcode.png\" type=\"1\" class=\"barcode\" style=\"height:50px;width:180px;\" title=\"{@单据编号}\"></div></div><div style=\"height: 30px;\" id=\"row_4\" class=\"row border\"><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_9\" class=\"column\">单据编号：</div><div style=\"width: 120px; text-align: left; height: 30px; line-height: 30px;\" id=\"column_12\" class=\"column\">{@单据编号}</div><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_13\" class=\"column\">移出仓库：</div><div style=\"height: 30px; line-height: 30px; text-align: left; width: 120px;\" id=\"column_14\" class=\"column\">{@移出仓库}</div><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_15\" class=\"column\">移入仓库：</div><div style=\"height: 30px; line-height: 30px; width: 120px; text-align: left;\" id=\"column_16\" class=\"column\">{@移入仓库}</div></div></div><div type=\"table\" nodel=\"1\" id=\"report_table_body\" class=\"group\" title=\"表格\"><table id=\"table_1\" class=\"table\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td style=\"width: 130px;\" class=\"td_title\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_th_69\">商品名称</div></td><td style=\"width: 130px;\" class=\"td_title\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_th_70\">商品编码</div></td><td style=\"width: 80px;\" class=\"td_title\"><div style=\"width: 80px;\" class=\"td_column\" id=\"column_th_71\">规格1</div></td><td style=\"width: 80px;\" class=\"td_title\"><div style=\"width: 80px; height: 20px; line-height: 20px;\" class=\"td_column\" id=\"column_th_72\">规格2</div></td><td style=\"width: 130px;\" class=\"td_title\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_th_73\">条形码</div></td><td style=\"width: 70px;\" class=\"td_title\"><div style=\"width: 70px;\" class=\"td_column\" id=\"column_th_74\">移出数量</div></td><td style=\"width: 90px;\" class=\"td_title\"><div style=\"width: 90px;\" class=\"td_column\" id=\"column_th_75\">库位</div></td></tr><!--detail_list--></table></div><div id=\"report_table_bottom\" class=\"group\" title=\"表格尾\"><div nodel=\"1\" id=\"row_2\" class=\"row border\"><div style=\"width: 450px; text-align: left;\" id=\"column_6\" class=\"column\">合计</div><div style=\"width: 120px; text-align: left;\" id=\"column_22\" class=\"column\">{@商品总数量}</div></div></div><div id=\"report_bottom\" class=\"group\" title=\"报表尾\"><div style=\"height: 22px;\" nodel=\"1\" id=\"row_3\" class=\"row border\"><div style=\"width: 100px;\" id=\"column_7\" class=\"column\">打印人：</div><div style=\"height: 22px; line-height: 22px; width: 280px; text-align: left;\" id=\"column_54\" class=\"column\">{@打印人}</div><div style=\"height: 22px; line-height: 22px; width: 80px; text-align: center;\" id=\"column_55\" class=\"column\">打印时间：</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_56\" class=\"column\">{@打印时间}</div></div></div></div>', '<tr><td style=\"width: 130px;\" class=\"td_detail\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_td_69\">{#商品名称}</div></td><td style=\"width: 130px;\" class=\"td_detail\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_td_70\">{#商品编码}</div></td><td style=\"width: 80px;\" class=\"td_detail\"><div style=\"width: 80px;\" class=\"td_column\" id=\"column_td_71\">{#规格1}</div></td><td style=\"width: 80px;\" class=\"td_detail\"><div style=\"width: 80px;\" class=\"td_column\" id=\"column_td_72\">{#规格2}</div></td><td style=\"width: 130px;\" class=\"td_detail\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_td_73\">{#条形码}</div></td><td style=\"width: 70px;\" class=\"td_detail\"><div style=\"width: 70px;\" class=\"td_column\" id=\"column_td_74\">{#移出数量}</div></td><td style=\"width: 90px;\" class=\"td_detail\"><div style=\"width: 90px;\" class=\"td_column\" id=\"column_td_75\">{#库位}</div></td></tr>', '');"
);

$u['FSF-1997'] = array(
    "ALTER TABLE api_order_send ADD UNIQUE INDEX `tid_express_code` (`tid`(255), `express_no`) USING BTREE;",
    "ALTER TABLE b2b_lof_datail drop index _index_key;",
    "ALTER TABLE b2b_lof_datail ADD UNIQUE INDEX _index_key (`order_type`,`order_code`,`sku`,`lof_no`);",
    "ALTER TABLE goods_lof drop index _lof_no_index;",
    "ALTER TABLE goods_lof ADD UNIQUE INDEX _lof_no_index (`sku`,`lof_no`);",
    "ALTER TABLE wms_b2b_order_lof drop index idx_record_code_type;",
    "ALTER TABLE wms_b2b_order_lof ADD UNIQUE INDEX idx_record_code_type (`record_code`,`record_type`,`barcode`,`lof_no`);",
    "ALTER TABLE wms_oms_order_lof drop index idx_record_code_type;",
    "ALTER TABLE wms_oms_order_lof ADD UNIQUE INDEX idx_record_code_type (`record_code`,`record_type`,`barcode`,`lof_no`);",
    "ALTER TABLE oms_sell_record_lof drop index _index_key;",
    "ALTER TABLE oms_sell_record_lof ADD UNIQUE INDEX _index_key (`record_code`,`record_type`,`sku`,`lof_no`);",
    "ALTER TABLE stm_profit_loss_lof drop index _index_key;",
    "ALTER TABLE stm_profit_loss_lof ADD UNIQUE INDEX _index_key (`take_stock_record_code`,`store_code`,`sku`,`lof_no`);",
    "ALTER TABLE goods_inv_lof drop index _index_key;",
    "ALTER TABLE goods_inv_lof ADD UNIQUE INDEX _index_key (`sku`,`store_code`,`lof_no`);",
);

$u['FSF-2002'] = array(
    "ALTER TABLE sys_params MODIFY COLUMN `value` varchar(255) NOT NULL DEFAULT '' COMMENT '参数值';",
    "UPDATE sys_params SET value='60' WHERE param_code='no_sync_bili' AND value='30'",
    "insert into sys_params(param_code,parent_code,param_name,type,form_desc,`value`,remark,memo) values('notice_set','0','通知设置','group','','','','');",
    "insert into sys_params(param_code,parent_code,param_name,type,form_desc,`value`,remark,memo) values('notice_email','notice_set','异常接收邮箱','text','','','','设置异常消息接收的邮箱地址。可设置多个邮箱地址，以;隔开');",
);