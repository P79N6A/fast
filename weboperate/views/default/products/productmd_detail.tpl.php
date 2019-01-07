<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看产品成员',
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'成员', 'type'=>'select_pop', 'field'=>'pcm_user', 'select'=>'sys/orguser','show_scene'=>'add,edit'),
                        array('title'=>'岗位', 'type'=>'select', 'field'=>'pcm_user_post','data'=>ds_get_select('post',2,array('post_state'=>'1'))),
		),      
		'hidden_fields'=>array(array('field'=>'pcm_id'), array('field'=>'pcm_cp_id', 'value'=>$request['cpid']),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'products/productmd/do_edit', //edit,add,view
	'act_add'=>'products/productmd/do_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('pcm_user', 'require'), 
                    array('pcm_user_post', 'require'), 
                    ) ,        //有效性验证
        //'callback'=>'submitCall'
)); ?>
