<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑增值服务类别',
	'links'=>array(
		'market/valueservice_cat/do_list'=>'增值服务类别信息'
	)
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'类别代码', 'type'=>'input', 'field'=>'vc_code', 'edit_scene'=>'add'),
			array('title'=>'类别名称', 'type'=>'input', 'field'=>'vc_name', ),
                        array('title'=>'关联产品', 'type'=>'select', 'field'=>'vc_cp_id', 'data' => ds_get_select('chanpin', 2)),
                        array('title'=>'优先级', 'type'=>'select', 'field'=>'vc_order','data'=>ds_get_select_by_field('ordertype',3) ),
                        array('title'=>'备注', 'type'=>'textarea', 'field'=>'vc_bz'),
		), 
		'hidden_fields'=>array(array('field'=>'vc_id'), array('field'=>'vc_code'),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'market/valueservice_cat/do_edit', //edit,add,view
	'act_add'=>'market/valueservice_cat/do_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('vc_code', 'require'), 
                    array('vc_name', 'require'),
                    array('vc_cp_id', 'require')), 
)); ?>