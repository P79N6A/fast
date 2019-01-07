<?php render_control('PageHead', 'head1',
array( 'title' => '修改密码'));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                    
//			array('title'=>'客户名称', 'type'=>'select','field'=>'kh_id','data'=>ds_get_select('kehu',2)),
                        array('title'=>'密码类型', 'type'=>'select', 'field'=>'pass_type','data'=>ds_get_select_by_field('passtype',3),'remark'=>'请选择重置的用户'),
                        array('title'=>'新密码', 'type'=>'password', 'field'=>'newpass', ),
                        array('title'=>'密码确认', 'type'=>'password', 'field'=>'newpass2', ),
                        ),
        'hidden_fields'=>array(array('field'=>'host_id')), 
	), 
	'buttons'=>array(
			array('label'=>'确认', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
   ),
	'act_edit'=>'clients/aliinfo/forreset_pass',
	'data'=>$response['data'],
        'rules'=>'clients/change_pass',        //对应方法在conf/validator/clients_conf.php,密码有效性验证
)); ?>