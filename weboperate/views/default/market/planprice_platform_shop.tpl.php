<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看型号配置',
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'平台名称', 'type'=>'select', 'field'=>'pd_pt_id','data' => ds_get_select('shop_platform', 2)),
                        array('title'=>'默认店铺数', 'type'=>'input', 'field'=>'pd_shop_amount', ),
                        array('title'=>'店铺单价', 'type'=>'input', 'field'=>'pd_shop_price', ),
		),      
		'hidden_fields'=>array(array('field'=>'pd_id'), array('field'=>'pd_price_id', 'value'=>$request['priceid']),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'market/planprice/do_platshop_edit', //edit,add,view
	'act_add'=>'market/planprice/do_platshop_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('pd_pt_id', 'require'), 
                    array('pd_shop_amount', 'require'),
                    array('pd_shop_price', 'require'),
                    ) ,        //有效性验证
        //'callback'=>'submitCall'
)); ?>
