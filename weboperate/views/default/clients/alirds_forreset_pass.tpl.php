<?php render_control('PageHead', 'head1',
array( 'title' => '修改密码'));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
//			array('title'=>'客户名称', 'type'=>'select','field'=>'kh_id','data'=>ds_get_select('kehu',2)),
                        array('title'=>'新密码', 'type'=>'password', 'field'=>'rds_newpass', ),
                        array('title'=>'密码确认', 'type'=>'password', 'field'=>'rds_newpass2', ),
                        ),
        'hidden_fields'=>array(array('field'=>'rds_id')), 
	), 
	'buttons'=>array(
			array('label'=>'确认', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
   ),
	'act_edit'=>'clients/alirds/forreset_pass',
	'data'=>$response['data'], 
        'rules'=>'clients/change_rds_pass',        //对应方法在conf/validator/clients_conf.php,密码有效性验证
)); 
?>
