<?php
$table = array(
    array(
        "title" => "",
	    "data" => "<a class='first_click' value='{\"shop_code\":\"<<shop_code>>\",\"print_data_type\":\"<<print_data_type>>\"}'></a>",
	    "class" => "wd30"
    ),
	array(
		"title" => "商店名称",
		"class"=>"wd200",
		"data" => array('data'=>'shop_code','phpfun'=>'get_shop_name_by_code')
	),
);