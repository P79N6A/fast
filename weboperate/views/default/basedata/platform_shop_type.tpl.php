<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看型号配置',
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
//			array('title'=>'平台名称', 'type'=>'input', 'field'=>'pd_pt_id_name', 'edit_scene'=>'add'),
                        array('title'=>'店铺类型', 'type'=>'input', 'field'=>'pd_shop_type', ),
		),      
		'hidden_fields'=>array(array('field'=>'pd_id'), array('field'=>'pd_pt_id', 'value'=>$request['pd_pt_id']),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'basedata/platform/do_platshop_edit', //edit,add,view
	'act_add'=>'basedata/platform/do_platshop_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('pd_shop_type', 'require'), 
                    ) ,        //有效性验证
        //'callback'=>'submitCall'
)); ?>
