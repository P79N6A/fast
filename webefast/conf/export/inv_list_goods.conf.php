<?php

$ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
$property_power = $ret_cfg['property_power'];
if ($property_power) {
    $property_data = load_model('prm/GoodsPropertyModel')->get_property_val('property_val_title,property_val');
}
$inv_export = array(
    'store_code_name' =>
    array(
        'title' => '仓库名称',
    ),
    'goods_name' =>
    array(
        'title' => '商品名称', 'type' => 1
    ),
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => 1
    ),
    'category_name' =>
    array(
        'title' => '商品分类',
    ),
    'effec_num' =>
    array(
        'title' => '可用库存',
    ),
    'lock_num' =>
    array(
        'title' => '实物锁定',
    ),
    'stock_num' =>
    array(
        'title' => '实物库存',
    ),
    'road_num' =>
    array(
        'title' => '在途库存',
    ),
    'out_num' =>
    array(
        'title' => '缺货库存',
    ),
    'safe_num' =>
    array(
        'title' => '安全库存',
    ),
);
if ($property_power) {
    foreach ($property_data as $val) {
        $inv_export[$val['property_val']]['title'] = $val['property_val_title'];
    }
}
return $inv_export;
