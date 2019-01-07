<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑用户',
	'links'=>array(
		'sys/user/do_list'=>'用户列表'
	)
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'登录名', 'type'=>'input', 'field'=>'user_code', 'edit_scene'=>'add',),
			array('title'=>'真实名', 'type'=>'input', 'field'=>'user_name', ),
                        array('title'=>'电话号码','type'=>'input','field'=>'phone'),
		
			array('title'=>'初始密码', 'type'=>'html','html'=>'baota8888'),
		),
		'hidden_fields'=>array(array('field'=>'user_id'), array('field'=>'user_code'), array('field'=>'custom_code','value' =>$request['custom_code'])),
	),
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	//'act_edit'=>'sys/user/do_edit', //edit,add,view
	'act_add'=>'base/custom/do_user_add',
	'data'=>isset($response['data'])?$response['data']:'',
	'rules'=>array(
		array('user_code', 'require'),
                array('user_name', 'require'),
		array('user_code', 'minlength', 'value'=>5),
	)
)); ?>
