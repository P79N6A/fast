<?php render_control('PageHead', 'head1',array('title'=>'设置后请刷新页面'));  ?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'别名', 'type'=>'input', 'field'=>'name'),
		),
		'hidden_fields'=>array(array('field'=>'goods_rule_id')),
	),
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'sys/goods_rule/do_edit', //edit,add,view
	'data'=>$response['data'],

	'rules'=>array(
		array('name', 'require'),
	),
)); ?>


