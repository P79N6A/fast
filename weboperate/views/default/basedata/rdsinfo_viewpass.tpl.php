<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '密码管理',
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
//			array('title'=>'客户名称', 'type'=>'select','field'=>'kh_id','data'=>ds_get_select('kehu',2)),
                        array('title'=>'RDS用户名', 'type'=>'input', 'field'=>'rds_user', ),
                        array('title'=>'RDS密码', 'type'=>'input', 'field'=>'rds_pass', ),
                        ),      
	), 
	'data'=>$response['data'],
)); ?>