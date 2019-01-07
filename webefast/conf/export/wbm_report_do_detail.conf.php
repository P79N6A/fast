<?php

$export_list = array(
    'custom_name' =>
    array(
        'title' => '分销商',
    ),
    'goods_name' =>
    array(
        'title' => '商品名称', 'type' => 1
    ),
    'spec1_name' =>
    array(
        'title' => '颜色', 'type' => 1
    ),
    'spec2_name' =>
    array(
        'title' => '尺码', 'type' => 1
    ),
    'barcode' =>
    array(
        'title' => '商品条形码', 'type' => '1'
    ),
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => '1'
    ),
    'brand_name' =>
    array(
        'title' => '品牌', 'type' => 1
    ),
    'category_name' =>
    array(
        'title' => '分类',
    ),
    'season_name' =>
    array(
        'title' => '季节',
    ),
    'year_name' =>
    array(
        'title' => '年份',
    ),
    'out_num' =>
    array(
        'title' => '出库数量',
    ),
    'out_money' =>
    array(
        'title' => '出库商品总金额',
    ),
    'in_num' =>
    array(
        'title' => '退货数量',
    ),
    'in_money' =>
    array(
        'title' => '退货商品总金额',
    ),
    'sell_price' =>
    array(
        'title' => '吊牌价',
    ),
);
$ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
$property_power = $ret_cfg['property_power'];
if ($property_power) {
    $property_data = load_model('prm/GoodsPropertyModel')->get_property_val('property_val_title,property_val');
}
if ($property_power) {
    foreach ($property_data as $val) {
        $export_list[$val['property_val']]['title'] = $val['property_val_title'];
        $export_list[$val['property_val']]['type'] = 1;
    }
}
return $export_list;
