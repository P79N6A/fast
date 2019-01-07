<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑季节',
	'links'=>array(
		'base/season/do_list'=>'季节列表'
	)
));?>
 <?php 
  if ($response['app_scene'] == 'add')
  $remark = "一旦保存不能修改";
  else 
  $remark = "";
 ?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'代码', 'type'=>'input', 'field'=>'season_code', 'remark'=>$remark,'edit_scene'=>'add'),
			array('title'=>'名称', 'type'=>'input', 'field'=>'season_name'),
		), 
		'hidden_fields'=>array(array('field'=>'season_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'base/season/do_edit', //edit,add,view
	'act_add'=>'base/season/do_add',
	'data'=>$response['data'],
	
	'rules'=>array(
		array('season_code', 'require'),
		array('season_name','require'),
	),
)); ?>

