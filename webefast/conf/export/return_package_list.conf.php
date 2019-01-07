<?php

$arr = array('goods_spec1', 'goods_spec2');
$arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
$response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '规格1';
$response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '规格2';
return array(
    'return_order_status_txt' =>
    array(
        'title' => '状态',
    ),
    'is_exchange_goods_str' =>
    array(
        'title' => '是否换货'
    ),
    'return_package_code' =>
    array(
        'title' => '包裹单号',
    ),
    'stock_date' =>
    array(
        'title' => '业务日期',
    ),
    'sell_return_code' =>
    array(
        'title' => '关联退单号', 'type' => 1
    ),
    'deal_code' =>
    array(
        'title' => '交易号', 'type' => 1
    ),
    'shop_code_name' =>
    array(
        'title' => '店铺名称',
    ),
    'return_express_code_name' =>
    array(
        'title' => '配送方式',
    ),
    'return_express_no' =>
    array(
        'title' => '快递单号', 'type' => 1
    ),
    'return_mobile' =>
    array(
        'title' => '手机号', 'type' => 1
    ),
    'buyer_name' =>
    array(
        'title' => '买家昵称', 'type' => 1
    ),
    'return_name' =>
    array(
        'title' => '退货人', 'type' => 1
    ),
    'remark' =>
    array(
        'title' => '备注', 'type' => 1
    ),
    'barcode' =>
    array(
        'title' => '条形码', 'type' => 1
    ),
    'apply_num' =>
    array(
        'title' => '卖家申请退货数',
    ),
    'num' =>
    array(
        'title' => '实际退货数（入库数）',
    ),
    'goods_name' =>
    array(
        'title' => '商品名称', 'type' => 1
    ),
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => 1
    ),
    'spec1_code' =>
    array(
        'title' => $response['goods_spec1_rename'] . '编码',
    ),
    'spec1_name' =>
    array(
        'title' => $response['goods_spec1_rename'] . '名称',
    ),
    'spec2_code' =>
    array(
        'title' => $response['goods_spec2_rename'] . '编码',
    ),
    'spec2_name' =>
    array(
        'title' => $response['goods_spec2_rename'] . '名称',
    ),
    'receive_person' =>
    array(
        'title' => '确认收货人',
    ),
    'receive_time' =>
    array(
        'title' => '验收入库时间',
    ),
);
