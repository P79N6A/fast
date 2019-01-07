<?php

return array(
    'shop_name' =>
    array(
        'title' => '店铺',
    ),
    'sale_channel_code' =>
    array(
        'title' => '平台',
    ),
    'deal_code_list' =>
    array(
        'title' => '交易号', 'is_sensitive' => array('oms_sell_record', 'deal_code_list'),
    ),
    'sell_record_code' =>
    array(
        'title' => '订单号', 'type' => 1
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
        'title' => '手机（电话）', 'is_sensitive' => array('oms_sell_record', 'receiver_mobile'),
    ),
    'receiver_address' =>
    array(
        'title' => '收货地址', 'is_sensitive' => array('oms_sell_record', 'receiver_address'),
    ),
    'receive_status' =>
    array(
        'title' => '是否快递交接',
    ),
    'order_sign_status' =>
    array(
        'title' => '是否签收',
    ),
    'sign_time' =>
    array(
        'title' => '签收时间',
        'type' => 1
    ),
    'store_name' =>
    array(
        'title' => '仓库',
    ),
    'express_code_name' =>
    array(
        'title' => '配送方式',
    ),
    'express_no' =>
    array(
        'title' => '快递单号', 'type' => 1
    ),
    'express_money' =>
    array(
        'title' => '快递费',
    ),
    'goods_weigh' =>
    array(
        'title' => '订单理论重量(Kg)', 'type' => 1,
    ),
    'real_weigh' =>
    array(
        'title' => '称重重量(Kg)', 'type' => 1,
    ),
    'weigh_express_money' =>
    array(
        'title' => '称重运费', 'type' => 1,
    ),
    'paid_money' =>
    array(
        'title' => '已付款',
    ),
    'invoice_title' =>
    array(
        'title' => '发票抬头', 'type' => 1
    ),
    'seller_remark' =>
    array(
        'title' => '商家留言', 'type' => 1
    ),
    'buyer_remark' =>
    array(
        'title' => '买家留言', 'type' => 1
    ),
    'store_remark' =>
    array(
        'title' => '仓库留言', 'type' => 1
    ),
    'record_time' =>
    array(
        'title' => '下单时间', 'type' => 1,
    ),
    'delivery_time' =>
    array(
        'title' => '发货时间', 'type' => 1,
    ),
    'is_notice_time' =>
    array(
        'title' => '通知配货时间', 'type' => 1,
    ),
    'embrace_time' =>
    array(
        'title' => '揽件时间', 'type' => 1,
    ),
    'is_return' =>
    array(
        'title' => '有无退单',
    ),
    'is_change_record' =>
    array(
        'title' => '换货单',
    ),
    'goods_num' =>
    array(
        'title' => '商品数量', 'type' => 1
    ),
    'order_remark' =>
    array(
        'title' => '订单备注', 'type' => 1
    ),
    'goods_name' =>
    array(
        'title' => '商品名称', 'type' => 1
    ),
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => 1
    ),
    'barcode' =>
    array(
        'title' => '商品条形码', 'type' => 1
    ),
    'spec1_name' =>
    array(
        'title' => '规格1', 'type' => 1
    ),
    'spec2_name' =>
    array(
        'title' => '规格2', 'type' => 1
    ),
    'num' =>
    array(
        'title' => '数量',
    ),
    'goods_price' =>
    array(
        'title' => '吊牌价',
    ),
    'avg_money' =>
    array(
        'title' => '均摊金额',
    ),
);
