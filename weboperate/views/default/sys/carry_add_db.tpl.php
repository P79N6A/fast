<?php render_control('PageHead', 'head1',
array('title'=> '添加结转库',
//	'links'=>array(
//		array('url'=>'market/valueservice/do_list','title'=>'增值服务列表')
//	)
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'标记名称', 'type'=>'input', 'field'=>'carry_name', 'edit_scene'=>'add'),
			array('title'=>'结转库名', 'type'=>'input', 'field'=>'db_name', 'edit_scene'=>'add'),
                        array('title'=>'对应RDS', 'type'=>'select', 'field'=>'rds_id','data'=>$response['select_rds']  ),
		), 
		'hidden_fields'=>array(array('field'=>'carry_db_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	//'act_edit'=>'market/valueservice_cat/do_edit', //edit,add,view
	'act_add'=>'sys/carry/do_add_db&app_fmt=json',
        'callback'=>'add_ok',
	'data'=>$response['data'],
        'rules'=>array(
                    array('db_name', 'require'), 
            ), 
)); ?>

<script type="text/javascript">
    function add_ok(data, id) {
        if (data.status > 1) {
            alert('添加成功');
            window.location.reload();
        } else {
            alert(data.message);

        }
    }
</script>