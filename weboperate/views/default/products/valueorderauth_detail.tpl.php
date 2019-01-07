<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '增值服务授权详情',
	'links'=>array(
		array('url'=>'products/valueorderauth/do_list','title'=>'增值授权列表')
	)
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'客户名称', 'type'=>'input', 'field'=>'vra_kh_id_name','show_scene'=>'view','edit_scene'=>''),
                        array('title'=>'产品名称', 'type'=>'input', 'field'=>'vra_cp_id_name',),
                        array('title'=>'产品版本',  'type'=>'select', 'field'=>'vra_pt_version','data' => ds_get_select_by_field('product_version', 2)),
                        array('title'=>'增值服务', 'type'=>'input', 'field'=>'vra_server_id_name',),
                        array('title'=>'开始时间', 'type'=>'input', 'field'=>'vra_startdate',),
                        array('title'=>'结束时间', 'type'=>'input', 'field'=>'vra_enddate',),
                        array('title'=>'授权状态', 'type'=>'checkbox', 'field'=>'vra_state', 'show_scene'=>'view'),
                        array('title'=>'备注', 'type'=>'textarea', 'field'=>'vra_bz', ),
        		),      
		'hidden_fields'=>array(array('field'=>'vra_id'),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'act_edit'=>'products/valueorderauth/do_edit', //edit,add,view
	'act_add'=>'products/valueorderauth/do_add',
	'data'=>$response['data'],
)); ?>
