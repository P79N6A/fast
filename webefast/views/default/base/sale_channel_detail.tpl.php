<?php render_control('PageHead', 'head1',
    array(
        'title' => isset($app['title']) ? $app['title'] : '编辑销售平台',
        'links' => array('base/sale_channel/do_list' => '销售平台列表',)
    ));
?>
<?php render_control('Form', 'payment_form', array(
    'conf' => array(
        'fields' => array(
            array('title' => '渠道代码', 'type' => 'input', 'field' => 'sale_channel_code'),
            array('title' => '渠道名称', 'type' => 'input', 'field' => 'sale_channel_name'),
            //array('title' => '渠道类型', 'type' => 'select', 'field' => 'sale_channel_type', 'data' => array_from_dict(array('0' => '自定义', '1' => '系统定义'))),
            array('title' => '是否启用', 'type' => 'checkbox', 'field' => 'is_active'),
            array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
        ),
        'hidden_fields' => array(
            array('field' => 'sale_channel_id'),
            array('field' => 'sale_channel_type', 'value' => '0'),
        ),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/sale_channel/do_edit', // edit,add,view
    'act_add' => 'base/sale_channel/do_add',
    'data' => $response['data'],
    'rules' => array(
        array('sale_channel_code,sale_channel_name', 'require'),
        array('sale_channel_code', 'match', 'value' => '/^[A-Za-z0-9]+$/', 'message' => '代码只允许字母和数字组成'),
    ),
));

?>