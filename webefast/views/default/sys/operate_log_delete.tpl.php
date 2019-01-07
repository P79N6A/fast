<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑品牌',
	'links'=>array(
		'prm/brand/do_list'=>'品牌列表'
	)
));?>
<?php 
$data = array();
if(isset($response['data']) && $response['data'] != ''){
	$data = $response['data'];
}
render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			
			array('title'=>'时间', 'type'=>'select', 'field'=>'range','data'=>$response['range']),
		), 
		
	), 
	'buttons'=>array(
			array('label'=>'删除', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	
	'act_add'=>'sys/operate_log/do_delete',
	'data'=>$data,
	
	'rules'=>array(
		array('range', 'require'),
		
	),
)); ?>



