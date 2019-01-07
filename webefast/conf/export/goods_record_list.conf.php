<?php

$ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
$property_power = $ret_cfg['property_power'];
if ($property_power) {
    $property_data = load_model('prm/GoodsPropertyModel')->get_property_val('property_val_title,property_val');
}
$goods_export = array(
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => '1'
    ),
    'goods_name' =>
    array(
        'title' => '商品名称', 'type' => 1
    ),
    'goods_short_name' =>
    array(
        'title' => '商品简称', 'type' => 1
    ),
    'goods_produce_name' =>
    array(
        'title' => '出厂名称', 'type' => 1
    ),
    'category_name' =>
    array(
        'title' => '分类',
    ),
    'goods_prop' =>
    array(
        'title' => '商品属性',
    ),
    'state' =>
    array(
        'title' => '商品状态',
    ),
    'brand_name' =>
    array(
        'title' => '品牌',
    ),
    'season_name' =>
    array(
        'title' => '季节',
    ),
    'year_name' =>
    array(
        'title' => '年份',
    ),
    'sell_price' =>
    array(
        'title' => '吊牌价',
    ),
    'cost_price' =>
    array(
        'title' => '成本价',
    ),
    'trade_price' =>
    array(
        'title' => '批发价',
    ),
    'purchase_price' =>
    array(
        'title' => '进货价',
    ),
    'min_price' =>
    array(
        'title' => '最低售价',
    ),
    'weight' =>
    array(
        'title' => '重量',
    ),
    'spec1_name' =>
    array(
        'title' => '规格1', 'type' => 1
    ),
    'spec2_name' =>
    array(
        'title' => '规格2', 'type' => 1
    ),
    'diy' =>
    array(
        'title' => '是否组装商品', 'type' => 3
    ),
    'status' =>
    array(
        'title' => '启用状态', 'type' => 4, 'func' => 'goods_status'
    ),
    'goods_desc' =>
    array(
        'title' => '商品描述', 'type' => 1
    ),
);
if ($property_power) {
    foreach ($property_data as $val) {
        $goods_export[$val['property_val']]['title'] = $val['property_val_title'];
    }
}
return $goods_export;
