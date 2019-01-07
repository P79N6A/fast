<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看产品平台KEY',
	'links'=>array(
		array('url'=>'sys/session/index','title'=>'产品平台KEY列表')
	)
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'产品', 'type'=>'select', 'field'=>'relation_product', 'edit_scene'=>'add','data'=>ds_get_select('chanpin')),
			array('title'=>'平台', 'type'=>'select', 'field'=>'relation_platform','edit_scene'=>'add','data'=>ds_get_select('shop_platform')),
			array('title'=>'企业KEY', 'type'=>'input', 'field'=>'rdsapp_key', 'value'=>$response['data']['app_key']),
                        array('title'=>'企业密钥', 'type'=>'input', 'field'=>'rdsapp_secret',  'value'=>$response['data']['app_secret']),
                        array('title'=>'企业SESSION', 'type'=>'input', 'field'=>'access_token', ),
                       array('title'=>'企业应用名称', 'type'=>'input', 'field'=>'app_nick', ),
                        array('title'=>'备注', 'type'=>'textarea', 'field'=>'memo',),
		), 
		'hidden_fields'=>array(array('field'=>'rds_id'),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>'2',
	'act_edit'=>'sys/session/do_edit', //edit,add,view
	'act_add'=>'sys/session/do_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('relation_product', 'require'), 
                    array('relation_platform', 'require'),
                    array('app_key', 'require'),
                    array('app_secret', 'require'),
                    array('access_token', 'require'),)
)); ?>