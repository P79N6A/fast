<?php
$operate = array(
	"shop_edit_print"=>"<a onclick='addTab(\"<<danju_print_name>>打印设置\",\"".cutoverUrl("edit_print")."&shop_print_id=<<shop_print_id>>&danju_print_code=<<danju_print_code>>&shop_code=<<shop_code>>\")'>编辑</a>",
	"shop_setDefaultPaper"=>"<a onclick='danju_template.shop_setDefaultPaper(<<shop_print_id>>)'>设置纸张</a>",
	"shop_setDefaultPrint"=>"<a onclick=\"danju_template.set_shop_default_print_data_type('<<print_data_type>>','<<shop_print_id>>','<<shop_code>>')\">设置默认</a>",
	"shop_selectPrinter"=>"<a onclick=\"modSettingPrinter.shop_selectPrinter(<<shop_print_id>>)\">修改打印机</a>",
	"set_shop_enable"=>"<a onclick=\"danju_template.set_shop_enable(<<shop_print_id>>)\">启用</a>",
	"set_shop_disable"=>"<a onclick=\"danju_template.set_shop_disable(<<shop_print_id>>)\">停用</a>",
	"sync_main"=>"<a onclick=\"danju_template.sync_main(<<shop_print_id>>)\">同步主模板</a>",
	'copy_shop_print' => "<a onclick=\"dlg_copy_shop_print(<<shop_print_id>>)\">复制</a>",
	'paste_shop_print' => "<a onclick=\"select_paste_shop_print('<<shop_print_id>>','<<danju_print_code>>')\">粘贴</a>",

	"display" => array(
		//设置默认
		array(
			"operate" => "shop_setDefaultPrint", "key" => "is_default", "value" => "0",
		),
		//启用
		array(
			"operate" => "set_shop_enable", "key" => "is_enable", "value" => "0",
		),
		//停用
		array(
			"operate" => "set_shop_disable", "key" => "is_enable", "value" => "1",
		),
	)
);

$table = array(

	array(
		"title" => "模板名称",
		"class" => "wd100",
		"data" => "danju_print_name"
	),
	array(
		"title" => "纸张类型",
		"class" => "wd60",
		"data" => array('data'=>'template_page_style','phpfun'=>'get_page_style_name')
	),
	array(
		"title" => "纸张宽度",
		"class" => "wd50",
		"data" => 'template_page_width'
	),
	
	array(
		"title" => "纸张高度",
		"class" => "wd50",
		"data" => 'template_page_height'
	),
	array(
		"title" => "打印机",
		"class" => "wd150",
		"data" => 'printer_name'
	),

	array(
		"class"=>"wd30",
		"title" => "启用",
		"data" => array("data" => "is_enable", "phpfun" => "get_record_status_table")
	),

);


function get_page_style_name($page_style) {
	if ('custom_pager' ==$page_style)
		return '自定义纸张';
	else
		return $page_style;
}
