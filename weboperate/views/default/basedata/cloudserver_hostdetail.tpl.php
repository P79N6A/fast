<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看型号配置',
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'型号名称', 'type'=>'input', 'field'=>'cm_host_type', ),
                        array('title'=>'CPU', 'type'=>'input', 'field'=>'cm_host_cpu', ),
                        array('title'=>'内存', 'type'=>'input', 'field'=>'cm_host_mem', ),
                        array('title'=>'硬盘', 'type'=>'input', 'field'=>'cm_host_disk', ),
                        array('title'=>'带宽', 'type'=>'input', 'field'=>'cm_host_net', ),
		),      
		'hidden_fields'=>array(array('field'=>'cm_id'), array('field'=>'cm_cd_id', 'value'=>$request['cdid']),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'basedata/cloudserver/do_host_edit', //edit,add,view
	'act_add'=>'basedata/cloudserver/do_host_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('cm_host_type', 'require'), 
                    ) ,        //有效性验证
        //'callback'=>'submitCall'
)); ?>
