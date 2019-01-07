<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑年份',
	'links'=>array(
		'base/season/do_list'=>'年份列表'
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
			array('title'=>'代码', 'type'=>'input', 'field'=>'year_code', 'remark'=>$remark,'edit_scene'=>'add'),
			array('title'=>'名称', 'type'=>'input', 'field'=>'year_name'),
		), 
		'hidden_fields'=>array(array('field'=>'year_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'base/year/do_edit', //edit,add,view
	'act_add'=>'base/year/do_add',
	'data'=>$response['data'],
	
	'rules'=>array(
		array('year_code', 'require'),
		array('year_name','require'),
	),
)); ?>


