<?php
$u['1990'] = array(
    "UPDATE `sys_role` SET `role_desc` = '该角色为系统内置，不可删除，拥有订单查询/售后服务单/会员列表/外包仓零售单导出明文权限' WHERE `role_code` = 'security';",
);
$u['1989']=array(
    "INSERT INTO `sys_print_templates` (
	`print_templates_code`,
	`print_templates_name`,
	`company_code`,
	`type`,
	`is_buildin`,
	`offset_top`,
	`offset_left`,
	`paper_width`,
	`paper_height`,
	`printer`,
	`template_val`,
	`template_body`,
	`template_body_replace`,
	`template_body_default`
)
SELECT 
	
		'kye',
		'跨越速运_普通模板',
		'KYE',
		'1',
		'1',
		'0',
		'0',
		'0',
		'0',
		'无',
		'',
		'LODOP.PRINT_INITA(\"0mm\",\"0mm\",886,500,\"跨越速运\");\r\nLODOP.SET_PRINT_PAGESIZE(0,2300,1270,\"\");\r\nLODOP.ADD_PRINT_SETUP_BKIMG(\"<img border=\'0\' src=\'http://121.41.48.87/efast_logo/KYSY.jpg\'/>\");\r\nLODOP.SET_SHOW_MODE(\"BKIMG_LEFT\",1);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_TOP\",1);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_WIDTH\",872);\r\nLODOP.SET_SHOW_MODE(\"BKIMG_HEIGHT\",480);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_phone\",240,177,174,24,c[\"sender_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_shop_name\",141,138,268,20,c[\"sender_shop_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",102,492,118,25,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"print_time\",394,157,150,20,c[\"print_time\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",238,537,228,25,c[\"receiver_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"sender\",103,125,113,24,c[\"sender\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"buyer_name\",137,503,271,22,c[\"buyer_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_addr\",202,71,334,25,c[\"sender_addr\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_province\",170,120,64,25,c[\"sender_province\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_city\",170,200,78,25,c[\"sender_city\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"senderr_district\",169,295,67,25,c[\"senderr_district\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_addr\",204,483,288,25,c[\"receiver_addr\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_province\",169,483,68,25,c[\"receiver_province\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_city\",169,573,69,25,c[\"receiver_city\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_district\",168,656,81,25,c[\"receiver_district\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",12);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\n',
		'',
		''
FROM dual WHERE not exists (select * from sys_print_templates where print_templates_code='kye');"
);
$u['1994'] = array(
    "INSERT INTO `sys_params` (`param_code`,`parent_code`,`param_name`,`type`,`form_desc`,`value`,`sort`,`remark`,`memo`)VALUES('fx_jiesuan_out','finance','S005_005 分销订单包含非分销商品，不允许结算','radio','[\"关闭\",\"开启\"]','0','0','1-开启 0-关闭','默认不开启，开启后，分销订单包含非分销商品，不允许结算');"
);
$u['1981'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `memo`) VALUES ( 'fx_auto_confirm', 'oms_property', '分销订单结算后自动确认', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭','默认不开启，开启后，分销订单结算后自动确认');"
);
$u['1988'] = array(
    "INSERT INTO `sys_schedule` (`code`,`name`,`status`,`type`,`desc`,`request`,`path`,`max_num`,`add_time`,`loop_time`,`task_type`,`task_module`,`plan_exec_time`)VALUES('return_order_to_delete','未确认的售后服务单自动作废','0','0','开启后，请在系统参数设置中设置未确认的售后服务单自动作废的天数(默认为90天)。设置后，创建后超过设置天数未确认的售后服务单将会被作废。此服务默认24小时执行一次','{\"app_act\":\"oms/sell_return/return_order_to_delete\",\"app_fmt\":\"json\"}','webefast/web/index.php','0','0','86400','0','sys','1470036253');",
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `memo`) VALUES ('', 'return_order_to_delete', 'oms_property', '未确认的售后服务单自动作废', 'text', '', '90', '0.00', '未确认的售后服务单自动作废', '需在自动服务设置中开启“未确认的售后服务单自动作废”的自动设置后，此参数才会起效。');"
);


