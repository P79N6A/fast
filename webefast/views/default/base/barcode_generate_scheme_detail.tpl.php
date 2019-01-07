<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑年份',
	'links'=>array(
		'base/season/do_list'=>'年份列表'
	)
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'方案名称', 'type'=>'input', 'field'=>'name', ),
			array('title'=>'模式', 'type'=>'input', 'field'=>'mode'),
			array('title'=>'分割符号', 'type'=>'input', 'field'=>'separate_sign'),
			array('title'=>'参照对象（位置1）', 'type'=>'input', 'field'=>'refer1'),
			array('title'=>'分隔符1', 'type'=>'input', 'field'=>'fenge1'),
			array('title'=>'参照对象（位置2）', 'type'=>'input', 'field'=>'refer2'),
			array('title'=>'分隔符2', 'type'=>'input', 'field'=>'fenge2'),
			array('title'=>'参照对象（位置3）', 'type'=>'input', 'field'=>'refer3'),
			array('title'=>'分隔符3', 'type'=>'input', 'field'=>'fenge3'),
			array('title'=>'参照对象（位置4）', 'type'=>'input', 'field'=>'refer4'),
			array('title'=>'分隔符4', 'type'=>'input', 'field'=>'fenge4'),
			array('title'=>'参照对象（位置5）', 'type'=>'input', 'field'=>'refer5'),
			array('title'=>'随机长度（位置5）', 'type'=>'input', 'field'=>'length', ),
			array('title' => '是否启用', 'type' => 'checkbox', 'field' => 'is_active'),
			array('title' => '描述', 'type' => 'textarea', 'field' => 'note'),
		), 
		'hidden_fields'=>array(array('field'=>'scheme_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'base/barcode_generate_scheme/do_edit', //edit,add,view
	'act_add'=>'base/barcode_generate_scheme/do_add',
	'data'=>$response['data'],
	
	'rules'=>array(
		array('name', 'require'),
		array('separate_sign', 'require'),
		//array('separate_sign','match','value'=>'/(?!.*\\|.*bbb|.*\')^.*$/i'),
	),
)); ?>


