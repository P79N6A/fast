<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑渠道',
	'links'=>array(
		array('url'=>'basedata/sellchannel/do_list',title=>'销售渠道')
	)
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'渠道名称', 'type'=>'input', 'field'=>'channel_name', ),
                        array('title'=>'渠道类型', 'type'=>'select', 'field'=>'channel_type','data'=>ds_get_select_by_field('channel_type',2)),
                        array('title'=>'渠道模式', 'type'=>'select', 'field'=>'channel_mode','data'=>ds_get_select_by_field('channel_mode',2)),
                        array('title'=>'描述', 'type'=>'textarea', 'field'=>'channel_desc', ),
                        ),      
		'hidden_fields'=>array(array('field'=>'channel_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>2,
	'act_edit'=>'basedata/sellchannel/do_edit', //edit,add,view
	'act_add'=>'basedata/sellchannel/do_add',
	'data'=>$response['data'],
         'rules'=>'basedata/add_channel',        //对应方法在conf/validator/basedata_conf.php,新建销售渠道必填字段验证。
)); ?>