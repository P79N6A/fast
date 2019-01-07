<?php

$u = array();

$u['151'] = array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'is_policy_store_safe_inv', 'op', '仓库适配启用安全库存', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '', '0000-00-00 00:00:00', '');",
);
$u['155'] = array(
    "INSERT INTO `base_express` (`company_code`, `express_code`, `express_name`, `type`, `area_type`, `tel`, `status`, `is_cash_on_delivery`, `sys`, `goods_img`, `is_add_person`, `is_add_time`, `is_edit_person`, `is_edit_time`, `print`, `printer_name`, `remark`, `reg_mail_no`, `calc_type`, `base_fee`, `base_weight`, `per_fee`, `per_weight`, `free_fee`, `per_rule`, `zk`, `free_per_weight`, `print_type`, `rm_id`, `rm_shop_code`, `df_id`, `pt_id`, `lastchanged`) SELECT 'OTHER', 'KYE', '跨越速运', '0', '0', '', '1', '0', '1', '', '', NULL, '', NULL, NULL, NULL, '', '', '0', '0.000', '0.00', '0.000', '0.00', '0.000', '0.000', NULL, NULL, '0', NULL, '', NULL, NULL, '2016-03-29 09:46:30' from dual where not exists (select * from base_express WHERE express_code='KYE');",
);


$u['139'] = array(
    "update sys_action set status=0 where    action_id in ('12020000','12030000');",
    "update sys_action set status=1  where action_id='12020000' AND (select count(1) from erp_config where erp_system=0)>0;",
    "update sys_action set status=1  where action_id='12030000' AND (select count(1) from erp_config where erp_system=1)>0;",
);


$u['166'] = array(
    "ALTER TABLE `wms_archive`
    MODIFY COLUMN `code`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '代码' AFTER `type`,
    ADD COLUMN `sys_code`  varchar(128) NULL AFTER `code`,
    ADD COLUMN `api_code`  varchar(128) NULL AFTER `sys_code`;
",
);

$u['142'] = array(
    "INSERT INTO `sys_action` VALUES ('2050300','2050000','url','店员列表','base/shop_clerk/do_list','1','1','0','1','2');",
    "ALTER TABLE `sys_user` ADD relation_shop VARCHAR(64) DEFAULT '' COMMENT '关联店铺代码' AFTER `type`;",
    "ALTER TABLE `sys_user` ADD create_person VARCHAR(50) DEFAULT '' COMMENT '创建人';",
    "ALTER TABLE `sys_user` ADD create_time datetime NOT NULL COMMENT '创建时间';",
    "ALTER TABLE `sys_user` MODIFY `type` INT(4) DEFAULT '0' COMMENT '0:普通账户 1：店长 2：收银员 3:导购员';",
);
$u['168'] = array(
    "ALTER TABLE api_bserp_item_quantity 
        ADD COLUMN `sku` varchar(255) DEFAULT '' COMMENT 'sku',
        ADD COLUMN `barcode` varchar(255) DEFAULT '' COMMENT 'barcode';",
);
$u['152'] = array(
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('jd', '京东快递', NULL, '1', '1', '0', '0', '1000', '1140', '无', '', 'LODOP.PRINT_INITA(\"0mm\",\"0mm\",480,450,\"京东快递_电子面单\");\r\nLODOP.SET_PRINT_PAGESIZE(0,1000,1140,\"\");\r\nLODOP.ADD_PRINT_SETUP_BKIMG(\"<img border=\'0\' src=\'http://img02.taobaocdn.com/imgextra/i2/775277144/TB2uGTKaVXXXXa3XpXXXXXXXXXX-775277144.jpg?category=express&id=jd_e.jpg\'/>\");\r\nLODOP.SET_SHOW_MODE(\"BKIMG_LEFT\",1);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_TOP\",1);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_WIDTH\",377);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_HEIGHT\",431);\r\nLODOP.ADD_PRINT_TEXTA(\"express_no\",253,166,159,16,c[\"express_no\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_phone\",91,158,116,18,c[\"receiver_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.ADD_PRINT_TEXTA(\"sell_record_code\",281,9,200,16,c[\"sell_record_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",110,138,111,18,c[\"receiver_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no\",359,269,96,55,\"128C\",c[\"express_no\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no_pack_no\",26,50,263,50,\"128Auto\",c[\"express_no_pack_no\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",112,23,61,16,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_address\",127,27,248,42,c[\"receiver_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_shop_name\",348,58,173,18,c[\"sender_shop_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_mobile\",366,263,116,15,c[\"sender_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.ADD_PRINT_TEXTA(\"sender_phone\",351,260,116,15,c[\"sender_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.ADD_PRINT_TEXTA(\"print_time\",381,276,87,13,c[\"print_time\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_address\",368,33,232,30,c[\"sender_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"detail:barcode|detail:num\",173,41,236,56,\"条形码*数量件\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"buyer_remark\",297,13,253,26,c[\"buyer_remark\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"seller_remark\",327,12,253,26,c[\"seller_remark\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",93,16,112,17,\"收件人信息\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\n', '{\"LODOP.ADD_PRINT_TEXTA(\\\"detail:barcode|detail:num\\\",173,41,236,56,\\\"\\u6761\\u5f62\\u7801*\\u6570\\u91cf\\u4ef6\\\");\":\"var detailstr=\\\"\\\";\\nfor(var i in c[\\\"detail\\\"]){\\nvar detail=c[\\\"detail\\\"][i];\\ndetailstr+=\\\"\\\"+detail[\\\"barcode\\\"]+\\\" *\\\"+detail[\\\"num\\\"]+\\\" \\u4ef6\\\"\\n}\\nLODOP.ADD_PRINT_TEXTA(\\\"detail:barcode|detail:num\\\",173,41,236,56,detailstr);\"}', '');",
    "INSERT INTO `base_express` (`express_id`, `company_code`, `express_code`, `express_name`, `type`, `area_type`, `tel`, `status`, `is_cash_on_delivery`, `sys`, `goods_img`, `is_add_person`, `is_add_time`, `is_edit_person`, `is_edit_time`, `print`, `printer_name`, `remark`, `reg_mail_no`, `calc_type`, `base_fee`, `base_weight`, `per_fee`, `per_weight`, `free_fee`, `per_rule`, `zk`, `free_per_weight`, `print_type`, `rm_id`, `rm_shop_code`, `df_id`, `pt_id`, `lastchanged`) VALUES ('67', 'JD', 'JD', '京东快递', '0', '0', '', '1', '0', '1', '', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '', '', '', '0', '0.000', '0.00', '0.000', '0.00', '0.000', '0.000', '1.000', '0.00', '0', '0', '', '0', '0', '');"
);


$u['178']  = array(
    "INSERT INTO base_area(id,type,name,parent_id,url,catch) VALUES(440309000000,4,'龙华新区','440300000000','03/440309.html','1');",
    "INSERT INTO base_area(id,type,name,parent_id,url,catch) VALUES(440310000000,4,'大鹏新区','440300000000','03/440310.html','1');",
    "INSERT INTO base_area(id,type,name,parent_id,url,catch) VALUES(440311000000,4,'光明新区','440300000000','03/440311.html','1');",
    "INSERT INTO base_area(id,type,name,parent_id,url,catch) VALUES(440312000000,4,'坪山新区','440300000000','03/440312.html','1');"
);

$u['192'] = array(
        "ALTER TABLE `oms_sell_record_detail`
    ADD INDEX `goods_code_index` (`goods_code`) USING BTREE ,
    ADD INDEX `sku_index` (`sku`) USING BTREE ;
    ",
         "ALTER TABLE `oms_sell_record_lof`
    ADD INDEX `occupy_type_index` (`occupy_type`) USING BTREE ,
    ADD INDEX `lastchanged` (`lastchanged`) USING BTREE ;
    ",
         "ALTER TABLE `oms_sell_record`
    ADD INDEX `is_problem` (`is_problem`) USING BTREE ;",
);


$u['193'] = array(
    "INSERT ignore INTO `base_express_company` (`company_id`, `company_code`, `company_name`, `rule`, `sys`, `is_active`, `remark`, `lastchanged`) VALUES ('65', 'DBKD', '德邦快递', '','1','0','', '2015-07-02 09:31:33');
",
);