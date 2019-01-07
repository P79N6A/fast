<?php render_control('PageHead', 'head1',
    array('title' => isset($app['title']) ? $app['title'] : '编辑问题原因',
        'links' => array('base/order_problem_reason/do_list' => '问题原因列表',
        )
    ));
?>
<?php render_control('Form', 'payment_form', array('conf' => array('fields' => array(
    array('title' => '问题原因代码', 'type' => 'input', 'field' => 'problem_reason_code'),
    array('title' => '问题原因名称', 'type' => 'input', 'field' => 'problem_reason_name'),
    array('title' => '是否启用', 'type' => 'checkbox', 'field' => 'is_active'),
    array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
),
    'hidden_fields' => array(array('field' => 'problem_reason_id')),
),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/order_problem_reason/do_edit', // edit,add,view
    'act_add' => 'base/order_problem_reason/do_add',
    'data' => $response['data'],
));

?>