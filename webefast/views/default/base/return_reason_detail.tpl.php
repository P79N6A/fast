<?php render_control('PageHead', 'head1',
    array('title' => isset($app['title']) ? $app['title'] : '编辑退货原因',
        'links' => array('base/return_reason/do_list' => '退货原因',
        )
    ));
?>
<?php render_control('Form', 'payment_form', array('conf' => array('fields' => array(
    array('title' => '代码', 'type' => 'input', 'field' => 'return_reason_code','edit_scene'=>'add'),
    array('title' => '名称', 'type' => 'input', 'field' => 'return_reason_name'),
    array('title' => '类型', 'type' => 'select', 'field' => 'return_reason_type', 'data' => array_from_dict(array('1' => '销售', '2' => '采购', '3' => '批发'))),
//    array('title' => '是否启用', 'type' => 'checkbox', 'field' => 'is_active'),
    array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
),
    'hidden_fields' => array(array('field' => 'return_reason_id')),
),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/return_reason/do_edit', // edit,add,view
    'act_add' => 'base/return_reason/do_add',
    'data' => $response['data'],
    'rules'=>array(
		array('return_reason_code', 'require'),
        array('return_reason_name', 'require'),
        array('return_reason_type', 'require'),
	),
	//'event'=>array('beforesubmit'=>'function (ev) {alert(1);return false;}')
));

?>