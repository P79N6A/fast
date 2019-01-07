<?php

return array(
    /*
      'status_text' =>
      array (
      'title' => '订单状态',
      ),
     */
    'sell_record_code' =>
    array(
        'title' => '订单编号', 'type' => '1',
    ),
    'is_pending_time' =>
    array(
        'title' => '挂起时间', 'type' => '1',
    ),
    'is_unpending_time' =>
    array(
        'title' => '自动解挂时间', 'type' => '1',
    ),
    'is_pending_name' =>
    array(
        'title' => '挂起原因',
    ),
    'sale_channel_name' =>
    array(
        'title' => '平台',
    ),
    'shop_name' =>
    array(
        'title' => '店铺',
    ),
    'deal_code_list' =>
    array(
        'title' => '交易号', 'type' => '1', 'is_sensitive' => array('oms_sell_record', 'deal_code_list'),
    ),
    'receiver_name' =>
    array(
        'title' => '收货人', 'is_sensitive' => array('oms_sell_record', 'receiver_name'),
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
    'record_time' =>
    array(
        'title' => '下单时间',
    ),
    'seller_remark' =>
    array(
        'title' => '商家留言', 'type' => 1
    ),
    'buyer_remark' =>
    array(
        'title' => '买家留言', 'type' => 1
    ),
    'is_pending_memo' =>
    array(
        'title' => '挂起备注', 'type' => 1
    ),
);
