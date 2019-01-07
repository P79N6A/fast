<?php

render_control('FormTable', 'form2', array(
    'conf' => array(
        'fields' => array(
            array('title' => '买家昵称', 'type' => 'label', 'field' => 'buyer_name'),
            array('title' => '收货人姓名', 'type' => 'label', 'field' => 'receiver_name'),
            array('title' => '收货人手机号码', 'type' => 'label', 'field' => 'receiver_phone'),
            array('title' => '收货人地址', 'type' => 'label', 'field' => 'receiver_address'),
        ),
        'hidden_fields' => array(
            array('field' => 'record_code', 'value' => $response['record']['record_code']),
        ),
    ),
    'act_edit' => '',
    'col' => 2,
    'per' => '0.3',
    'buttons' => array(),
    'data' => $response['record'],
));
?>

