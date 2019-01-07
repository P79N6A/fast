<?php
    //支付方式：
//    $response['data']['is_online_name'] = $response['data']['is_online'] ? '在线支付' : '';
	$response['data']['is_online_name'] = '';
    $response['data']['is_vouch_name'] = $response['data']['is_vouch'] ? '担保交易' : '';
    $response['data']['is_cod_name'] = $response['data']['is_cod'] ? '货到付款' : '款到发货';
    $response['data']['pay_type'] = ltrim(implode(',', array($response['data']['is_online_name'], $response['data']['is_vouch_name'], $response['data']['is_cod_name'])), ',');
    
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '编辑支付方式',
    'links' => array('base/payment/do_list' => '支付方式列表'
    )
));
?>
<?php

render_control('Form', 'payment_form', array('conf' => array(
        'fields' => array(
            array('title' => '支付代码', 'type' => 'input', 'field' => 'pay_type_code', 'edit_scene' => 'add','width' => 150),
            array('title' => '支付名称', 'type' => 'input', 'field' => 'pay_type_name', 'edit_scene' => 'add','width' => 150),
            //array('title' => '手续费', 'type' => 'input', 'field' => 'charge'),
            //array('title' => '在线支付', 'type' => 'checkbox', 'field' => 'is_online'),
            //array('title' => '担保交易', 'type' => 'checkbox', 'field' => 'is_vouch'),
            //array('title' => '货到付款', 'type' => 'checkbox', 'field' => 'is_cod'),
            //array('title' => '支付类型', 'type' => 'radio_group', 'field' => 'pay_type', 'data' => ds_get_select_by_field('pay_type', 0)),
            array('title' => '支付类型', 'type' => 'input', 'field' => 'pay_type', 'width' => 150, 'align' => '','edit_scene' => 'add'),
            array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
        ),
        'hidden_fields' => array(array('field' => 'pay_type_id')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/payment/do_edit',
    //edit,add,view
    //'act_add'=>'base/payment/do_add',
    'data' => $response['data'],
    'rules' => array(
        array('pay_type_name', 'require'),
    ),
));
?>