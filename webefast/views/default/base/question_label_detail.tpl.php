<?php
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '编辑设问标签',
    'links' => array()
));
?>

<?php
render_control('Form', 'question_label_form', array('conf' => array('fields' => array(
            array('title' => '关键字', 'type' => 'textarea', 'field' => 'content', 'width' => '500', 'remark' => '提示：收货地址包含特定关键字的订单设问；多个关键字之间，用逗号(英文)隔开'),
        ),
        'hidden_fields' => array(array('field' => 'question_label_id')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/question_label/do_edit', // edit,add,view
    'act_add' => 'base/question_label/do_add', // edit,add,view
    'data' => $response['data'],
    'callback' => 'goto_edit',
    'rules' => array()
));
?>
<script type="text/javascript">
    function goto_edit(data) {
        BUI.Message.Alert(data.message);
    }
</script>