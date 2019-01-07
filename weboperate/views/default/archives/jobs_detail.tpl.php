<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑岗位',
	'links'=>array(
		'archives/jobs/do_list'=>'岗位信息'
	)
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'岗位序号', 'type'=>'input', 'field'=>'post_code', 'edit_scene'=>'add'),
			array('title'=>'岗位名称', 'type'=>'input', 'field'=>'post_name', ),
			array('title'=>'有效岗位', 'type'=>'checkbox', 'field'=>'post_state', 'show_scene'=>'view,add'),
		), 
		'hidden_fields'=>array(array('field'=>'post_id'), array('field'=>'post_code'),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'archives/jobs/jobs_edit', //edit,add,view
	'act_add'=>'archives/jobs/jobs_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('post_code', 'require'), 
                    array('post_name', 'require'))
)); ?>