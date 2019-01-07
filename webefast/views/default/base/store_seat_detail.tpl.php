<?php render_control('PageHead', 'head1',
    array('title' => isset($app['title']) ? $app['title'] : '编辑仓库库位',
        'links' => array('base/store_seat/do_list' => '仓库库位列表',
        )
    ));
?>
<?php render_control('Form', 'store_seat_form', array('conf' => array('fields' => array(
    array('title' => '仓库库位代码', 'type' => 'input', 'field' => 'store_seat_code'),
    array('title' => '仓库库位名称', 'type' => 'input', 'field' => 'store_seat_name'),
    array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
),
    'hidden_fields' => array(array('field' => 'store_seat_id')),
),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/store_seat/do_edit', // edit,add,view
    'act_add' => 'base/store_seat/do_add',
    'data' => $response['data'],
));

?>