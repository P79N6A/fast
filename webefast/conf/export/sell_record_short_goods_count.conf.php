<?php

$arr = array('goods_spec1', 'goods_spec2');
$arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
$response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
$response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
return array(
    'barcode' =>
    array(
        'title' => '条形码', 'type' => 1
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
        'title' => "{$response['goods_spec1_rename']}"
    ),
    'spec2_name' =>
    array(
        'title' => "{$response['goods_spec2_rename']}"
    ),
    'platform_spec' =>
    array(
        'title' => '平台规格', 'type' => 1
    ),
    'num_total' =>
    array(
        'title' => '商品数量'
    ),
    'short_num_total' =>
    array(
        'title' => '缺货数量'
    ),
);

