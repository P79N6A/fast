<?php

$fields = array(
    array('title' => '订单编号', 'type' => 'label', 'field' => 'record_code'),
    array('title' => '订单归属门店', 'type' => 'label', 'field' => 'offline_shop_name'),
    array('title' => '订单状态', 'type' => 'label', 'field' => 'status'),
    array('title' => '订单类型', 'type' => 'label', 'field' => 'record_type_name'),
    array('title' => '订单自提门店', 'type' => 'label', 'field' => 'offline_shop_name'),
    array('title' => '支付方式', 'type' => 'label', 'field' => 'pay_way_name'),
    array('title' => '订单应收', 'type' => 'label', 'field' => 'payable_amount'),
    array('title' => '顾客实付', 'type' => 'label', 'field' => 'buyer_real_amount'),
    array('title' => '订单优惠', 'type' => 'label', 'field' => 'discount_amount'),
    array('title' => '订单发货仓库', 'type' => 'label', 'field' => 'send_store_name'),
    array('title' => '导购员', 'type' => 'label', 'field' => 'guide_name'),
    array('title' => '收银员', 'type' => 'label', 'field' => 'cashier_name'),
    array('title' => '发货方式', 'type' => 'label', 'field' => 'send_way_name'),
    array('title' => '配送快递', 'type' => 'label', 'field' => 'express_name'),
    array('title' => '快递单号', 'type' => 'label', 'field' => 'express_no'),
);
if ($response['record']['record_type'] == 1) {
    $fields[] = array('title' => '网络店铺', 'type' => 'label', 'field' => 'online_shop_name');
    $fields[] = array('title' => '网络交易号', 'type' => 'label', 'field' => 'record_out_code');
    $fields[] = array('title' => '网络订单编号', 'type' => 'label', 'field' => 'record_code');
}
$fields[] = array('title' => '订单备注', 'type' => 'input', 'field' => 'remark');
$fields[] = array('title' => '', 'type' => '', 'field' => '');
$fields[] = array('title' => '', 'type' => '', 'field' => '');
render_control('FormTable', 'form1', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => array(
            array('field' => 'record_code', 'value' => $response['record']['record_code']),
        ),
    ),
    'act_edit' => '',
    'col' => 3,
    'per' => '0.4',
    'buttons' => array(),
    'data' => $response['record'],
));
?>