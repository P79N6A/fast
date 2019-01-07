<?php

return array(
    'sell_record_code' =>
    array(
        'title' => '订单号', 'type' => 1
    ),
    'delivery_time' =>
    array(
        'title' => '发货时间',
    ),
    'record_time' =>
    array(
        'title' => '下单时间'
    ),
    'pay_time' =>
    array(
        'title' => '付款时间',
    ),
    'pay_name' =>
    array(
        'title' => '支付方式',
    ),
    'alipay_no' =>
    array(
        'title' => '支付宝交易号',
        'type' => 1
    ),
    'express_name' =>
    array(
        'title' => '配送方式'
    ),
    'express_no' =>
    array(
        'title' => '物流单号', 'type' => 1
    ),
    'deal_code_list' =>
    array(
        'title' => '交易号', 'type' => 1
    ),
    'buyer_name' =>
    array(
        'title' => '会员名称', 'type' => 8
    ),
    'receiver_name' =>
    array(
        'title' => '收货人', 'type' => 8
    ),
    'receiver_mobile' =>
    array(
        'title' => '手机'
    ),
    'receiver_address' =>
    array(
        'title' => '收货地址', 'is_sensitive' => array('oms_sell_record', 'receiver_address'),
    ),
    'receiver_zip_code' =>
    array(
        'title' => '收货邮编',
    ),
    'sale_channel_name' =>
    array(
        'title' => '销售平台'
    ),
    'shop_name' =>
    array(
        'title' => '店铺',
    ),
    'express_money' =>
    array(
        'title' => '邮费',
    ),
    'goods_count' =>
    array(
        'title' => '数量',
    ),
    'avg_money_all' =>
    array(
        'title' => '商品应收总额',
    ),
    'paid_money' =>
    array(
        'title' => '已付金额',
    ),
    'goods_weigh' =>
    array(
        'title' => '订单理论重量(Kg)',
    ),
);
