<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看产品模块',
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'模块名称', 'type'=>'input', 'field'=>'pm_name', ),
                        array('title'=>'英文名称', 'type'=>'input', 'field'=>'pm_en_name', ),
                        array('title'=>'模块简称', 'type'=>'input', 'field'=>'pm_jc', ),
                        array('title'=>'描述', 'type'=>'textarea', 'field'=>'pm_memo', ),
		),      
		'hidden_fields'=>array(array('field'=>'pm_id'), array('field'=>'pm_cp_id', 'value'=>$request['cpid']),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'products/productmk/do_edit', //edit,add,view
	'act_add'=>'products/productmk/do_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('pm_name', 'require'), 
                    ) ,        //有效性验证
        //'callback'=>'submitCall'
)); ?>
