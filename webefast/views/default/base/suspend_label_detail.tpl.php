<style>
    .form-horizontal .control-label{
        width:110px;
    }    
    .form-horizontal .row .control-group{
        width:100%;
    }    
</style>
<?php render_control('PageHead', 'head1',
    array('title' => isset($app['title']) ? $app['title'] : '编辑挂起标签',
        'links' => array('base/suspend_label/do_list' => '挂起标签列表',
        )
    ));
?>
<?php render_control('Form', 'suspend_label_form', array('conf' => array(
     'fields' => array(
        array('title' => '类型代码', 'type' => 'input', 'field' => 'suspend_label_code','edit_scene'=>'add','remark' => $app['scene']=='add'?'一旦保存不能修改！':''),
        array('title' => '类型名称', 'type' => 'input', 'field' => 'suspend_label_name'),
        array('title' => '解挂时间(小时)', 'type' => 'input', 'field' => 'cancel_suspend_time','remark' => '订单挂起N小时后将自动解挂'),
        array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
     ),
    'hidden_fields' => array(array('field' => 'suspend_label_id')),
),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/suspend_label/do_edit', // edit,add,view
    'act_add' => 'base/suspend_label/do_add',
    'data' => $response['data'],
    'rules'=>array(
	array('suspend_label_code', 'require'),
	array('suspend_label_name', 'require'),
        array('cancel_suspend_time', 'number'),
        array('cancel_suspend_time', 'min' ,'value'=>0),
	),
));

?>