<?php

$export_list = array(
    'custom_name' =>
    array(
        'title' => '分销商', 'type' => 1
    ),
    'record_code' =>
    array(
        'title' => '单据编号', 'type' => 1
    ),
    'record_type_name' =>
    array(
        'title' => '单据类型', 'type' => 1
    ),
    'goods_name' =>
    array(
        'title' => '商品名称', 'type' => 1
    ),
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => 1
    ),
    'spec1_name' =>
    array(
        'title' => '规格1', 'type' => 1
    ),
    'spec2_name' =>
    array(
        'title' => '规格2', 'type' => 1
    ),
    'barcode' =>
    array(
        'title' => '商品条形码', 'type' => 1
    ),
    'brand_name' =>
    array(
        'title' => '品牌', 'type' => 1
    ),
    'category_name' =>
    array(
        'title' => '分类', 'type' => 1
    ),
    'season_name' =>
    array(
        'title' => '季节', 'type' => 1
    ),
    'year_name' =>
    array(
        'title' => '年份',
    ),
    'num' =>
    array(
        'title' => '数量',
    ),
    'money' =>
    array(
        'title' => '金额',
    ),
    'remark' =>
    array(
        'title' => '备注', 'type' => 1
    ),
    'store_name' =>
    array(
        'title' => '仓库',
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

