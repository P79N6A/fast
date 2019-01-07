<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '查看公告',
	'links'=>array(
		array('url'=>'sys/notice/do_list','title'=>'公告列表')
	)
));?>
<?php echo load_js('ueditor1_4_3/ueditor.config.js,ueditor1_4_3/ueditor.all.js')?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                    array('title'=>'公告标题', 'type'=>'input', 'field'=>'not_title',),
                    array('title'=>'公告类型', 'type'=>'select', 'field'=>'not_type','data' => ds_get_select_by_field('nottype', 3)),
                    array('title'=>'截至日期', 'type'=>'time', 'field'=>'not_enddate', ),
                    array('title'=>'公告详情', 'type'=>'richinput', 'field'=>'not_detail','span'=>20,),
                    array('title'=>'关联产品', 'type'=>'select', 'field'=>'not_cp_id','data'=>ds_get_select('chanpin')),
                    array('title'=>'链接地址', 'type'=>'input', 'field'=>'not_detail_url',),
                    array('title'=>'审核状态', 'type'=>'checkbox', 'field'=>'not_sh','edit_scene'=>'','show_scene'=>'view'),
                    array('title'=>'审核人', 'type'=>'input', 'field'=>'not_shuser_name','edit_scene'=>'','show_scene'=>'view'),
                    array('title'=>'审核日期', 'type'=>'date', 'field'=>'not_shdate','edit_scene'=>'','show_scene'=>'view'),
                    array('title'=>'创建人', 'type'=>'input', 'field'=>'not_createuser_name','edit_scene'=>'','show_scene'=>'view,edit'),
                    array('title'=>'创建日期', 'type'=>'date', 'field'=>'not_createdate','edit_scene'=>'','show_scene'=>'view,edit'),
                    array('title'=>'修改人', 'type'=>'input', 'field'=>'not_updateuser_name','edit_scene'=>'','show_scene'=>'view'),
                    array('title'=>'修改日期', 'type'=>'date', 'field'=>'not_updatedate','edit_scene'=>'','show_scene'=>'view'),
		),      
		'hidden_fields'=>array(array('field'=>'not_id')), 
	), 
	'buttons'=>array(
                array('label'=>'提交', 'type'=>'submit'),
                array('label'=>'重置', 'type'=>'reset'),
	),
        'col'=>3,
	'act_edit'=>'sys/notice/do_edit', //edit,add,view
	'act_add'=>'sys/notice/do_add',
	'data'=>$response['data'],
        'rules'=>array(
                    array('not_title', 'require'), 
                    array('not_type', 'require'), 
                    array('not_enddate', 'require'), 
                    array('not_detail', 'require'), 
                ),        //对应方法在conf/validator/clients_conf.php,新建客户必填字段验证。
)); ?>