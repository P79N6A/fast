<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看型号配置',
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'型号名称', 'type'=>'input', 'field'=>'cm_db_type', ),
                        array('title'=>'内存', 'type'=>'input', 'field'=>'cm_db_mem', ),
                        array('title'=>'容量', 'type'=>'input', 'field'=>'cm_db_disk', ),
                        array('title'=>'最大连接数', 'type'=>'input', 'field'=>'cm_max_con', ),
                        array('title'=>'QPS最大执行次数', 'type'=>'input', 'field'=>'cm_max_qps', ),
                        array('title'=>'IOPS每秒最大读写', 'type'=>'input', 'field'=>'cm_max_iops', ),
		),      
		'hidden_fields'=>array(array('field'=>'cm_id'), array('field'=>'cm_cd_id', 'value'=>$request['cdid']),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'basedata/cloudserver/do_db_edit', //edit,add,view
	'act_add'=>'basedata/cloudserver/do_db_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('cm_db_type', 'require'), 
                    ) ,        //有效性验证
)); ?>
