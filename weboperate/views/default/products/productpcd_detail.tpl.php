<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看补丁SQL明细',
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'版本编号', 'type'=>'select', 'field'=>'version_no','edit_scene'=>'', 'data' => ds_get_select('pdt_bh', 2)),
                        array('title'=>'版本补丁', 'type'=>'input', 'field'=>'version_patch','edit_scene'=>'' ),
                        array('title'=>'SQL内容', 'type'=>'textarea', 'field'=>'content', ),
                        array('title'=>'是否执行', 'type'=>'checkbox', 'field'=>'is_exec', ),
                        array('title'=>'关联任务', 'type'=>'input', 'field'=>'task_sn', ),
		),      
		'hidden_fields'=>array(array('field'=>'id'),
                        array('field'=>'hd_version_no','value'=>$response['data']['version_no']),
                        array('field'=>'hd_version_patch','value'=>$response['data']['version_patch']),
                        ), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'products/productpcd/do_edit', //edit,add,view
	'act_add'=>'products/productpcd/do_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('version_no', 'require'), 
                    array('version_patch', 'require'), 
                    array('content', 'require'), 
                    ) ,        //有效性验证
        //'callback'=>'submitCall'
)); ?>
