<?php
$u = array();
$u['286'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6030101', '6030100', 'act', '删除', 'pur/purchase_record/do_delete', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6030102', '6030100', 'act', '验收', 'pur/purchase_record/do_checkin', '2', '1', '0', '1', '0');" //采购入库单删除、验收增加权限控制
);

$u['279'] = array(
 "   DROP TABLE IF EXISTS `oms_sale_day`;",

"CREATE TABLE `oms_sale_day` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(128) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `sale_num` int(10) DEFAULT NULL,
  `sale_money` decimal(10,2) DEFAULT NULL,
  `sale_goods_num` int(10) DEFAULT NULL,
  `refund_num` int(10) DEFAULT NULL,
  `refund_goods_num` int(10) DEFAULT NULL,
  `refund_money` decimal(10,2) DEFAULT NULL,
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`shop_code`,`sale_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='单据月度销售分析';",

"DROP TABLE IF EXISTS `oms_sale_month_cat`;",

"CREATE TABLE `oms_sale_month_cat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(128) DEFAULT NULL,
  `date_year_month` varchar(50) DEFAULT NULL,
  `type` tinyint(3) DEFAULT '0' COMMENT '0分类，1品牌',
  `type_code` varchar(128) DEFAULT NULL,
  `sale_num` int(10) DEFAULT NULL,
  `sale_money` decimal(10,2) DEFAULT NULL,
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`shop_code`,`date_year_month`,`type`,`type_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='月度品牌分类销售百分百';",


"DROP TABLE IF EXISTS `oms_sale_month_goods`;",
    
"CREATE TABLE `oms_sale_month_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(128) DEFAULT NULL,
  `date_year_month` varchar(50) DEFAULT NULL,
  `type` tinyint(3) DEFAULT '0' COMMENT '0销售数量，1销售金额，2滞销',
  `goods_code` varchar(128) DEFAULT NULL,
  `sku` varchar(128) DEFAULT NULL,
  `sku_value` varchar(50) DEFAULT '0',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`shop_code`,`date_year_month`,`type`,`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='月度商品销售';",

);

$u['289'] = array(
    "CREATE TABLE `api_weipinhuijit_wms_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `notice_record_no` varchar(50) NOT NULL COMMENT '通知单号',
  `store_out_record_no` varchar(50) NOT NULL COMMENT '批发销货单号',
  `pick_ids` varchar(500) NOT NULL COMMENT 'pick_id编号',
  `delivery_id` varchar(50) DEFAULT NULL COMMENT '出库单Id',
  `tel` varchar(50) NOT NULL COMMENT '送货仓库',
  `brand_code` varchar(100) NOT NULL COMMENT '品牌',
  `express_code` varchar(50) DEFAULT NULL COMMENT '配送方式code',
  `express` varchar(50) NOT NULL DEFAULT '' COMMENT '快递单号',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `notice_record_no` (`notice_record_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
",
);

$u['285'] = array(
    "DELETE FROM sys_user_pref WHERE iid = 'rpt/report_jxc_do_list';",
);
$u['295'] = array(
  "UPDATE `sys_print_templates`
SET `template_body` = 'LODOP.PRINT_INITA(\"0mm\",\"0mm\",480,450,\"京东快递_电子面单\")
;\r\nLODOP.SET_PRINT_PAGESIZE(0,1000,1140,\"\");\r\nLODOP.ADD_PRINT_TEXTA(\"express_no\",251,95,182,21,c[\"express_no\"])
;\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",13);\r\nLODOP.ADD_PRINT_TEXTA
(\"receiver_mobile\",199,106,111,18,c[\"receiver_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no_pack_no\",14,11,359,50,\"128Auto\",c[\"express_no_pack_no\"]);\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no_pack_no\",301,17,216,37,\"128Auto\",c[\"express_no_pack_no\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",199,29,66,18,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_address\",166,29,180,17,c[\"receiver_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",164,5,20,63,\"客户信息\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(11,1,227,2,0,1);\r\nLODOP.ADD_PRINT_LINE(11,1,10,376,0,1);\r\nLODOP.ADD_PRINT_LINE(227,1,228,376,0,1);\r\nLODOP.ADD_PRINT_LINE(11,375,227,376,0,1);\r\nLODOP.ADD_PRINT_LINE(87,1,88,376,0,1);\r\nLODOP.ADD_PRINT_LINE(137,1,136,376,0,1);\r\nLODOP.ADD_PRINT_LINE(87,191,163,192,0,1);\r\nLODOP.ADD_PRINT_LINE(163,1,164,376,0,1);\r\nLODOP.ADD_PRINT_LINE(137,27,227,28,0,1);\r\nLODOP.ADD_PRINT_LINE(137,229,227,230,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",89,3,57,18,\"始发地：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",89,193,57,20,\"目的地：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(227,260,163,261,0,1);\r\nLODOP.ADD_PRINT_LINE(197,230,196,375,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",164,229,38,32,\"客户签字\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"payable_money\",200,269,105,21,c[\"payable_money\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",196,229,38,31,\"应收金额\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(247,1,375,169,0,1);\r\nLODOP.ADD_PRINT_LINE(274,1,275,376,0,1);\r\nLODOP.ADD_PRINT_LINE(339,1,340,376,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",275,2,73,21,\"客户信息：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(415,235,274,236,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",275,70,73,21,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",275,142,89,21,c[\"receiver_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",275,236,38,19,\"备注\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(375,1,376,376,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",339,236,44,14,\"商家ID：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",352,236,61,14,\"商家订单号：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",375,236,53,15,\"始发城市：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sell_record_code\",352,293,84,14,c[\"sell_record_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"initial\",89,53,130,18,c[\"initial\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"initial_code\",105,19,167,32,c[\"initial_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",20);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"end_point\",89,243,131,20,c[\"end_point\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"end_point_code\",108,193,183,29,c[\"end_point_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",20);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"real_weigh\",67,281,95,19,c[\"real_weigh\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"initial_order\",138,230,146,25,c[\"initial_order\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",16);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"print_time\",232,207,133,14,c[\"print_time\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"print_time\",417,200,113,17,c[\"print_time\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"shop_id_jd\",339,279,96,14,c[\"shop_id_jd\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",339,1,61,14,\"寄方信息：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender\",339,61,75,14,c[\"sender\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_phone\",339,135,85,14,c[\"sender_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_address\",352,1,237,15,c[\"sender_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"destination_site\",135,28,150,28,c[\"destination_site\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",16);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"road_area\",137,190,42,28,c[\"road_area\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",16);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_city\",376,293,81,25,c[\"sender_city\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\n'
WHERE
	(
		`print_templates_code` = 'jd'
	);
"  
);
$u['297'] = array(
    "alter table api_goods_sku add column `sys_goods_barcode` varchar(128) DEFAULT '' COMMENT '绑定系统条码';",
    "drop FUNCTION if exists `f_spec2_init`;",
    "CREATE FUNCTION `f_spec2_init`(p_spec_name varchar(50)) RETURNS varchar(50) CHARSET utf8
        BEGIN

        DECLARE spec_str_code varchar(50) default '';
        DECLARE spec_str_code_max varchar(50)  default '';

        select  `spec2_code` into spec_str_code from `base_spec2` where `spec2_name` = p_spec_name;

        if spec_str_code is null or spec_str_code = ''
        then
                select max(spec2_code) into spec_str_code_max from base_spec2 WHERE spec2_code LIKE '1%' or spec2_code like '2%'   limit 1; 

                if spec_str_code_max = '' or spec_str_code_max is null
                then 
                        set spec_str_code_max = 100;
                end if;

                set spec_str_code = spec_str_code_max + 1;

                INSERT INTO `base_spec2`  (`spec2_name`,`spec2_code`)  VALUES (p_spec_name,spec_str_code);

        end if;

        RETURN spec_str_code;
        END;",
    "drop FUNCTION if exists `f_spec1_init`;",
    "CREATE FUNCTION `f_spec1_init`(p_spec_name varchar(50)) RETURNS varchar(100) CHARSET utf8
        BEGIN
                #Routine body goes here...
        DECLARE spec1_str_code varchar(50) default '';
        DECLARE spec1_str_code_max varchar(50)  default '';

        select  `spec1_code` into spec1_str_code from `base_spec1` where `spec1_name` = p_spec_name;

        if spec1_str_code is null or spec1_str_code = ''
        then
                select max(spec1_code) into spec1_str_code_max from base_spec1 
        WHERE spec1_code LIKE '1%' or spec1_code LIKE '2%' or spec1_code LIKE '3%' or spec1_code LIKE '4%' or spec1_code LIKE '5%' limit 1; 

                if spec1_str_code_max = '' or spec1_str_code_max is null
                then 
                        set spec1_str_code_max = 100;
                end if;

                set spec1_str_code = spec1_str_code_max + 1;

                INSERT INTO `base_spec1`  (`spec1_name`,`spec1_code`)  VALUES (p_spec_name,spec1_str_code);

        end if;

        RETURN spec1_str_code;

        END;",
    "drop FUNCTION if exists `f_cat_init`;",
    "CREATE FUNCTION `f_cat_init`(p_cat_name varchar(50)) RETURNS varchar(50) CHARSET utf8
        BEGIN

        DECLARE spec_str_code varchar(50) default '';
        DECLARE spec_str_code_max varchar(50)  default '';

        select  `category_code` into spec_str_code from base_category where `category_name` = p_cat_name;

        if spec_str_code is null or spec_str_code = ''
        then
                select max(`category_code`) into spec_str_code_max from base_category WHERE `category_code` LIKE '1%'  limit 1; 

                if spec_str_code_max = '' or spec_str_code_max is null
                then 
                        set spec_str_code_max = 100;
                end if;

                set spec_str_code = spec_str_code_max + 1;

                INSERT INTO base_category  (`category_code`,`category_name`,p_code)  VALUES (spec_str_code,p_cat_name,0);

        end if;

        RETURN spec_str_code;
        END;",
    "drop FUNCTION if exists `f_brand_init`;",
    "CREATE FUNCTION `f_brand_init`(p_brand_name varchar(50)) RETURNS varchar(50) CHARSET utf8
        BEGIN

        DECLARE spec_str_code varchar(50) default '';
        DECLARE spec_str_code_max varchar(50)  default '';

        select  `brand_code` into spec_str_code from base_brand where `brand_name` = p_brand_name;

        if spec_str_code is null or spec_str_code = ''
        then
                select max(`brand_code`) into spec_str_code_max from base_brand WHERE `brand_code` LIKE '1%'  limit 1; 

                if spec_str_code_max = '' or spec_str_code_max is null
                then 
                        set spec_str_code_max = 100;
                end if;

                set spec_str_code = spec_str_code_max + 1;

                INSERT INTO base_brand  (`brand_code`,`brand_name`)  VALUES (spec_str_code,p_brand_name);

        end if;

        RETURN spec_str_code;
        END;",
    "drop procedure if exists `tools_goods_init_from_taobao`;",
    "CREATE PROCEDURE `tools_goods_init_from_taobao`(
        p_code varchar(50),
        p_name varchar(50),
        p_cat_name varchar(50),
        p_brand_name varchar(50),
        p_price varchar(50),
        p_state int,
        p_barcode varchar(50),
        p_sku_desc varchar(150)
        ,p_sku_id varchar(50))
        mypro:BEGIN

        DECLARE s_goods_count varchar(50);
        DECLARE s_cat_code varchar(50);
        DECLARE s_cat_name varchar(50);

        DECLARE s_brand_code varchar(50);
        DECLARE s_brand_name varchar(50);

        DECLARE spec1_str varchar(150);
        DECLARE s_spec1_name varchar(50);

        DECLARE spec2_str varchar(150);
        DECLARE s_spec2_name varchar(50);

        DECLARE s_spec1_code varchar(50);
        DECLARE s_spec2_code varchar(50);
        DECLARE s_sku varchar(50);

        DECLARE s_respose varchar(50) default '1';

        if p_code = '' or p_code is null
        then 
                select  '商品编码不能为空';
                leave mypro;	
        end if;

        select count(1) into s_goods_count  from base_goods where goods_code = p_code;

        if p_brand_name = '' or p_brand_name is null
        then
                set s_brand_name = '默认';
        else
                set s_brand_name = p_brand_name;
        end if;

        if s_goods_count = 0
        then
                set s_cat_code = 	f_cat_init(p_cat_name);
                set s_brand_code = f_brand_init(s_brand_name);

                if s_cat_code is not null and s_brand_code is not null
                then
                        INSERT INTO base_goods (goods_code,goods_name,category_code,category_name,brand_code,brand_name,sell_code,sell_status,price,status,sell_price,state)
        VALUES (p_code,p_name,s_cat_code,p_cat_name,s_brand_code,s_brand_name,0,2,p_price,0,p_price,p_state);
                else
                        select '创建分类和品牌失败';
                        leave mypro;
                end if;
        end if;

        if  p_sku_desc = '' or p_sku_desc is null
        then
                set s_spec1_name = '通用';
                set s_spec2_name = '通用';
        else

        set spec1_str = SUBSTRING_INDEX(p_sku_desc,';',1);
        set s_spec1_name = SUBSTRING_INDEX(spec1_str,':',-1);

        set spec2_str = MID(p_sku_desc,CHAR_LENGTH(spec1_str)+1);
        set s_spec2_name = SUBSTRING_INDEX(spec2_str,':',-1);

        end if;



        set s_spec1_code = f_spec1_init(s_spec1_name);
        set s_spec2_code = f_spec2_init(s_spec2_name);

        set s_sku = concat(p_code,s_spec1_code,s_spec2_code);

        if  p_barcode = '' or p_barcode is null
        then
                set p_barcode = s_sku;
        end if;

        INSERT ignore INTO goods_sku (goods_code,spec1_code,spec1_name,spec2_code,spec2_name,sku,barcode)
        VALUES (p_code,s_spec1_code,s_spec1_name,s_spec2_code,s_spec2_name,s_sku,p_barcode);

        INSERT ignore INTO goods_barcode (goods_code,spec1_code,spec2_code,sku,barcode,add_time)
        VALUES (p_code,s_spec1_code,s_spec2_code,s_sku,p_barcode,now());

        if p_sku_id <>'' and p_sku_id is not null
        then

                update api_goods_sku set sys_goods_barcode = p_barcode where sku_id = p_sku_id;

        end if;

        select s_respose;

        END;",
    "drop procedure if exists `tools_goods_init_from_taobao_shop`;",
    "CREATE PROCEDURE `tools_goods_init_from_taobao_shop`(p_shop_code varchar(50))
        mypro:BEGIN

        DECLARE s_item_count int;
        DECLARE s_code varchar(50);
        DECLARE s_name varchar(50);
        DECLARE s_cat_name varchar(50);
        DECLARE s_brand_name varchar(50);
        DECLARE s_price varchar(50);
        DECLARE s_state int;
        DECLARE s_barcode varchar(50);
        DECLARE s_sku_desc varchar(150);

        DECLARE s_sku_id varchar(50);

         DECLARE done bool DEFAULT false;

            DECLARE curl CURSOR FOR 
        select api_goods.goods_code,api_goods.goods_name,api_goods.price,api_goods.cat,api_goods.brand,
        api_goods_sku.sku_properties_name,api_goods_sku.goods_barcode,api_goods.status ,api_goods_sku.sku_id  
        from api_goods,api_goods_sku where api_goods.shop_code =p_shop_code 
        and api_goods.goods_from_id = api_goods_sku.goods_from_id and api_goods.invalid_status = 1 and api_goods_sku.status = 1 ;

        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = true;  
            OPEN curl;  
            personLoop: LOOP  
              FETCH curl INTO s_code,s_name,s_price,s_cat_name,s_brand_name,s_sku_desc,s_barcode,s_state,s_sku_id;  
              IF done THEN  
                LEAVE personLoop;  
              ELSE  
                        call tools_goods_init_from_taobao(s_code,s_name,s_cat_name,s_brand_name,s_price,s_state,s_barcode,s_sku_desc,s_sku_id);
              END IF;  
            END LOOP personLoop;  
            CLOSE curl;  
        END;",
);

$u['281'] = array(
    "ALTER TABLE oms_shop_sell_record_detail ADD UNIQUE idxu_key (record_code,sku,is_gift);", //门店订单明细表增加唯一索引
);