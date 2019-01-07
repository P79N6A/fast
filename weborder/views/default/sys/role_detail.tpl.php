<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑角色',
	'links'=>array(
		'sys/role/do_list'=>'角色列表'
	)
));?>

<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'代码', 'type'=>'input', 'field'=>'role_code', 'validation'=>array('maxlen'=>30,), 'edit_scene'=>'add'),
			array('title'=>'名称', 'type'=>'input', 'field'=>'role_name', 'validation'=>array('type'=>'required','maxlen'=>150,)),
			array('title'=>'描述', 'type'=>'textarea', 'field'=>'role_desc', 'validation'=>array('maxlen'=>255,)),
		), 
		'hidden_fields'=>array(array('field'=>'role_id'), array('field'=>'role_code'),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'sys/role/do_edit',
	'act_add'=>'sys/role/do_add',
	'data'=>$response['data'],
)); ?>