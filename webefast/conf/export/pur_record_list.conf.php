<?php

$arr = array('goods_spec1', 'goods_spec2');
$arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
$response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
$response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
return array(
    'pur_num' =>
    array(
        'title' => '补货数',
    ),
    'goods_name' =>
    array(
        'title' => '商品名称', 'type' => 1
    ),
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => 1,
    ),
    'spec1_name' =>
    array(
        'title' => "{$response['goods_spec1_rename']}",
    ),
    'spec2_name' =>
    array(
        'title' => "{$response['goods_spec2_rename']}",
    ),
    'barcode' =>
    array(
        'title' => '商品条形码', 'type' => 1,
    ),
    'sale_week_num' =>
    array(
        'title' => '近7天日均销量',
    ),
    'sale_week_num_all' =>
    array(
        'title' => '近7天销量总量',
    ),
    'sale_month_num' =>
    array(
        'title' => '近30天日均销量',
    ),
    'sale_month_num_all' =>
    array(
        'title' => '近30天销量总量',
    ),
    'sale_two_month_num' =>
    array(
        'title' => '近60天日均销量',
    ),
    'sale_two_month_num_all' =>
    array(
        'title' => '近60天销量总量',
    ),
    'sale_three_month_num' =>
    array(
        'title' => '近90天日均销量',
    ),
    'sale_three_month_num_all' =>
    array(
        'title' => '近90天销量总量',
    ),
    'stock_num' =>
    array(
        'title' => '在库库存',
    ),
    'road_num' =>
    array(
        'title' => '在途库存',
    ),
    'inv_num' =>
    array(
        'title' => '可用库存',
    ),
    'wait_deliver_num' =>
    array(
        'title' => '已付款未发货数',
    ),
    'sell_price' =>
    array(
        'title' => '吊牌价',
    ),
    'brand_name' =>
    array(
        'title' => '品牌',
    ),
    'category_name' =>
    array(
        'title' => '分类',
    ),
    'season_name' =>
    array(
        'title' => '季节',
    ),
);
