<?php

return array(
    'shop_name' =>
    array(
        'title' => '店铺',
    ),
    'sale_channel_name' =>
    array(
        'title' => '平台',
    ),
    'deal_code_list' =>
    array(
        'title' => '交易号', 'type' => 1
    ),
    'seller_remark' =>
    array(
        'title' => '商家留言',
    ),
    'buyer_remark' =>
    array(
        'title' => '买家留言',
    ),
    'sell_record_code' =>
    array(
        'title' => '订单号', 'type' => 1
    ),
    'status' =>
    array(
        'title' => '状态', 'type' => 1
    ),
    'buyer_name' =>
    array(
        'title' => '买家昵称', 'is_sensitive' => array('oms_sell_record', 'buyer_name')
    ),
    'receiver_name' =>
    array(
        'title' => '收货人', 'is_sensitive' => array('oms_sell_record', 'receiver_name'),
    ),
    'receiver_mobile' =>
    array(
        'title' => '手机', 'type' => 1, 'is_sensitive' => array('oms_sell_record', 'receiver_mobile'),
    ),
    'receiver_address' =>
    array(
        'title' => '收货地址', 'is_sensitive' => array('oms_sell_record', 'receiver_address'),
    ),
    'store_name' =>
    array(
        'title' => '仓库',
    ),
    'express_name' =>
    array(
        'title' => '配送方式',
    ),
    'express_no' =>
    array(
        'title' => '快递单号', 'type' => 1
    ),
    'express_money' =>
    array(
        'title' => '运费',
    ),
    'payable_money' =>
    array(
        'title' => '应付款',
    ),
    'paid_money' =>
    array(
        'title' => '已付款',
    ),
    'record_time' =>
    array(
        'title' => '下单时间', 'type' => 1
    ),
    'pay_time' =>
    array(
        'title' => '付款时间', 'type' => 1
    ),
    'check_time' =>
    array(
        'title' => '确认时间', 'type' => 1
    ),
    'is_notice_time' =>
    array(
        'title' => '通知配货时间', 'type' => 1
    ),
    'delivery_time' =>
    array(
        'title' => '发货时间', 'type' => 1
    ),
    'confirm_person' =>
    array(
        'title' => '确认人',
    ),
    'goods_num' =>
    array(
        'title' => '数量',
    ),
    'order_remark' =>
    array(
        'title' => '备注', 'type' => 1
    ),
    'invoice_number' =>
    array(
        'title' => '发票号', 'type' => 1
    ),
);
