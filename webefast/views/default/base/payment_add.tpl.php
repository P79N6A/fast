<?php
render_control('Form', 'payment_form', array('conf' => array(
    'fields' => array(
                array('title' => '支付代码', 'type' => 'input', 'field' => 'pay_type_code', 'edit_scene' => 'add','remark' => '一旦保存不能修改！', 'width' => 150),
                array('title' => '支付名称', 'type' => 'input', 'field' => 'pay_type_name', 'edit_scene' => 'add', 'width' => 150),
                array('title' => '支付类型', 
                      'type' => 'select', 
                      'field' => 'pay_type', 
                      'width' => 150, 
                      'align' => '', 
                      'data' => ds_get_select_by_field('payment_type',0),
                      'edit_scene' => 'add'),
                array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
            ),
    ),
    'buttons' => array(
                array('label' => '提交', 'type' => 'submit'),
                array('label' => '重置', 'type' => 'reset'),
        ),
    'act_add'=>'base/payment/do_add',
    'data' => $response['data'],
    'rules' => array(
        array('pay_type_code', 'require'),
        array('pay_type_name', 'require'),
        array('pay_type', 'require'),
    ),
));
?>