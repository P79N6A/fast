<?php render_control('PageHead', 'head1',
array('title'=>'修改密码'));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'当前密码', 'type'=>'password', 'field'=>'old_user_pwd'),
                        array('title'=>'新密码', 'type'=>'password', 'field'=>'new_user_pwd'),
			array('title'=>'确认新密码', 'type'=>'password', 'field'=>'dnew_user_pwd'),
		), 
	), 
	'buttons'=>array(
			array('label'=>'确认', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'act_edit'=>'sys/user/do_chgpasswd',
	'data'=>$response['data'],
        'rules'=>'sys/user_chgpwd',        //有效性验证
)); ?>