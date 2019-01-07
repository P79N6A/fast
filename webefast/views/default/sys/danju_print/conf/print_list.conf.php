<?php
$operate = array(
	"edit_print"=>"<a onclick='addTab(\"<<danju_print_name>>打印设置\",\"".cutoverUrl("edit_print")."&print_id=<<print_id>>&danju_print_code=<<danju_print_code>>\")'>编辑</a>",
	"do_danju_shop_index_by_print_data_type"=>"<a onclick='addTab(\"商店单据模版设置\",\"".cutoverUrl("do_danju_shop_index_by_print_data_type")."&print_data_type=<<print_data_type>>\")'>商店设置</a>",
	//"view_print"=>"<a onclick='addTab(\"<<danju_print_name>>打印预览\",\"".cutoverUrl("view_print")."&print_id=<<print_id>>&danju_print_code=<<danju_print_code>>\")'>预览</a>",
	"setDefaultPaper"=>"<a onclick='danju_template.setDefaultPaper(<<print_id>>)'>设置纸张</a>",
	"setDefaultPrint"=>"<a onclick=\"danju_template.set_default_print_data_type('<<print_data_type>>',<<print_id>>)\">设置默认</a>",
	"selectPrinter"=>"<a onclick=\"modSettingPrinter.selectPrinter(<<print_id>>)\">修改打印机</a>",

	"display" => array(

		//设置默认
		array(
			"operate" => "setDefaultPrint", "key" => "is_default", "value" => "0",
		),
		array(
			"operate" => "do_danju_shop_index_by_print_data_type", "key" => "have_shop_setting", "value" => "1",
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
		"class" => "wd30",
		"title" => "默认",
		"data" => array("data" => "is_default", "phpfun" => "get_record_status_table")
	),

);


function get_page_style_name($page_style) {
	if ('custom_pager' ==$page_style)
		return '自定义纸张';
	else
		return $page_style;
}
