<?php

render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			//array('title'=>'店铺', 'type'=>'select', 'field'=>'shop','data' => $response['shop']),
		//	array('title'=>'下载类型', 'type'=>'select', 'field'=>'shop','data' => $response['shop']),
		),
//		'hidden_fields'=>array(array('field'=>'print_id')),
	),
	'buttons'=>array(
		array('label'=>'一键建档', 'type'=>'submit'),
		//array('label'=>'重置', 'type'=>'reset'),
	),
	'act_add'=>'api/base_item/do_one_click_create_goods', //edit,add,view

	'data'=>$response['data'],

	'rules'=>array(
//		array('order_time', 'require'),
//		array('reason','require'),
	),
)); ?>