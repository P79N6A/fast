<?php render_control('PageHead', 'head1',
    array('title' => isset($app['title']) ? $app['title'] : '编辑挂起原因',
        'links' => array('base/order_pause_reason/do_list' => '挂起原因列表',
        )
    ));
?>
<?php render_control('Form', 'payment_form', array('conf' => array('fields' => array(
    array('title' => '挂起原因代码', 'type' => 'input', 'field' => 'pause_reason_code'),
    array('title' => '挂起原因名称', 'type' => 'input', 'field' => 'pause_reason_name'),
    array('title' => '是否启用', 'type' => 'checkbox', 'field' => 'is_active'),
    array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
),
    'hidden_fields' => array(array('field' => 'pause_reason_id')),
),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/order_pause_reason/do_edit', // edit,add,view
    'act_add' => 'base/order_pause_reason/do_add',
    'data' => $response['data'],
));

?>