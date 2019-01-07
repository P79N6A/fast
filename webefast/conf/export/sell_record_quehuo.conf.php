<?php

return array(
    'sell_record_code' =>
    array(
        'title' => '订单号',
    ),
    'sale_channel_code' =>
    array(
        'title' => '平台',
    ),
    'shop_name' =>
    array(
        'title' => '店铺',
    ),
    'deal_code_list' =>
    array(
        'title' => '交易号', 'is_sensitive' => array('oms_sell_record', 'deal_code_list'),
    ),
    'receiver_name' =>
    array(
        'title' => '收货人', 'is_sensitive' => array('oms_sell_record', 'receiver_name')
    ),
    'receiver_address' =>
    array(
        'title' => '收货地址', 'is_sensitive' => array('oms_sell_record', 'receiver_address')
    ),
    'receiver_mobile' =>
    array(
        'title' => '手机号码', 'type' => 1
    ),
    'store_name' =>
    array(
        'title' => '仓库',
    ),
    'paid_money' =>
    array(
        'title' => '已付金额',
    ),
    'seller_remark' =>
    array(
        'title' => '商家留言', 'type' => 1
    ),
    'buyer_name' =>
    array(
        'title' => '买家昵称', 'is_sensitive' => array('oms_sell_record', 'buyer_name')
    ),
    'buyer_remark' =>
    array(
        'title' => '买家留言', 'type' => 1
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
        'title' => '商品条形码', 'type' => 1,
    ),
    'spec1_code' =>
    array(
        'title' => '颜色编码', 'type' => 1,
    ),
    'spec1_name' =>
    array(
        'title' => '颜色名称',
    ),
    'spec2_code' =>
    array(
        'title' => '尺码编码', 'type' => 1,
    ),
    'spec2_name' =>
    array(
        'title' => '尺码名称',
    ),
    'num' =>
    array(
        'title' => '数量',
    ),
    'short_num' =>
    array(
        'title' => '缺货数',
    ),
    'pay_time' =>
    array(
        'title' => '付款时间',
    ),
    'record_time' =>
    array(
        'title' => '下单时间',
    ),
    'platform_spec' =>
    array(
        'title' => '平台规格',
    ),
);
