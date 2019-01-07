<?php

$ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
$property_power = $ret_cfg['property_power'];
if ($property_power) {
    $property_data = load_model('prm/GoodsPropertyModel')->get_property_val('property_val_title,property_val');
}
$goods_export = array(
    'shop_name' =>
    array(
        'title' => '店铺',
    ),
    'goods_name' =>
    array(
        'title' => '商品名称', 'type' => 1
    ),
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => 1
    ),
    'all_goods_money' =>
    array(
        'title' => '销售应收金额',
    ),
    'real_count' =>
    array(
        'title' => '实际销售数量', 'type' => 1
    ),
    'goods_count' =>
    array(
        'title' => '销售数量',
    ),
    'return_count_num' =>
    array(
        'title' => '退货数量',
    ),
    'return_money_all' =>
    array(
        'title' => '退货应退金额',
    ),
        );
if ($property_power) {
    foreach ($property_data as $val) {
        $goods_export[$val['property_val']]['title'] = $val['property_val_title'];
    }
}
return $goods_export;


