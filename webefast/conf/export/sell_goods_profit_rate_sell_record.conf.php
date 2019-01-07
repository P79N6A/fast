<?php

$arr = array('goods_spec1', 'goods_spec2');
$arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
$response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
$response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
return array(
    'sell_record_code' =>
    array(
        'title' => '订单号', 'type' => 1
    ),
    'deal_code' =>
    array(
        'title' => '交易号', 'type' => 1
    ),
    'sale_channel_name' =>
    array(
        'title' => '销售平台',
    ),
    'shop_name' =>
    array(
        'title' => '店铺',
    ),
    'buyer_name' =>
    array(
        'title' => '会员昵称', 'type' => 1
    ),
    'delivery_time' =>
    array(
        'title' => '发货时间',
    ),
    'store_name' =>
    array(
        'title' => '仓库',
    ),
    'express_name' =>
    array(
        'title' => '配送方式',
    ),
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => 1
    ),
    'goods_name' =>
    array(
        'title' => '商品名称', 'type' => 1
    ),
    'spec1_name' =>
    array(
        'title' => "{$response['goods_spec1_rename']}", 'type' => 1
    ),
    'spec2_name' =>
    array(
        'title' => "{$response['goods_spec2_rename']}", 'type' => 1
    ),
    'season_name' =>
    array(
        'title' => '季节', 'type' => 1
    ),
    'category_name' =>
    array(
        'title' => '分类', 'type' => 1
    ),
    'barcode' =>
    array(
        'title' => '条形码', 'type' => 1
    ),
    'num' =>
    array(
        'title' => '商品数量',
    ),
    'avg_money' =>
    array(
        'title' => '商品销售额',
    ),
    'goods_cost_price' =>
    array(
        'title' => '商品成本',
    ),
    'goods_gross_profit' =>
    array(
        'title' => '商品毛利',
    ),
    'goods_gross_profit_rate' =>
    array(
        'title' => '商品毛利率',
    ),
    'goods_price' =>
    array(
        'title' => '吊牌价',
    ),
    'weight' =>
    array(
        'title' => '理论重量(Kg)', 'type' => 1
    ),
);



