<?php render_control('PageHead', 'head1',
    array('title' => isset($app['title']) ? $app['title'] : '编辑订单标签',
        'links' => array('base/return_label/do_list' => '订单标签列表',
        )
    ));
?>
<?php render_control('Form', 'return_label_form', array('conf' => array(
     'fields' => array(
        array('title' => '类型代码', 'type' => 'input', 'field' => 'return_label_code','edit_scene'=>'add','remark' => '一旦保存不能修改！'),
        array('title' => '类型名称', 'type' => 'input', 'field' => 'return_label_name'),
        array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
     ),
    'hidden_fields' => array(array('field' => 'return_label_id')),
),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/return_label/do_edit', // edit,add,view
    'act_add' => 'base/return_label/do_add',
    'data' => $response['data'],
    'rules'=>array(
	array('return_label_code', 'require'),
	array('return_label_name', 'require'),
	),
));

?>