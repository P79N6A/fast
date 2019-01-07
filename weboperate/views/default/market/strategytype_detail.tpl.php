<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看营销策略类型',
	'links'=>array(
		'market/strategytype/do_list'=>'营销策略类型列表'
	)
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'策略类型代码', 'type'=>'input', 'field'=>'st_code','edit_scene'=>'add'),
			array('title'=>'策略类型名称', 'type'=>'input', 'field'=>'st_name'),
			array('title'=>'描述', 'type'=>'textarea', 'field'=>'st_remark'),
		), 
		'hidden_fields'=>array(array('field'=>'st_id'), array('field'=>'st_code'),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
    	'act_edit'=>'market/strategytype/do_edit', //edit,add,view
	'act_add'=>'market/strategytype/do_add',
	'data'=>$response['data'],
)); ?>