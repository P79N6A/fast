<?php

$export_list = array(
    'record_code' =>
    array(
        'title' => '单据编号',
    ),
    'order_time' =>
    array(
        'title' => '下单时间', 'type' => 1
    ),
    'record_time' =>
    array(
        'title' => '业务日期', 'type' => 1
    ),
    'custom_name' =>
    array(
        'title' => '分销商',
    ),
    'store_name' =>
    array(
        'title' => '仓库',
    ),
    'name' =>
    array(
        'title' => '联系人',
    ),
    'tel' =>
    array(
        'title' => '电话', 'type' => 1
    ),
    'address' =>
    array(
        'title' => '地址', 'type' => 1
    ),
    'goods_name' =>
    array(
        'title' => '商品名称', 'type' => 1
    ),
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => 1
    ),
    'barcode' =>
    array(
        'title' => '商品条形码', 'type' => 1
    ),
    'sell_price' =>
    array(
        'title' => '吊牌价',
        'type' => 1
    ),
    'shelf_name' =>
    array(
        'title' => '库位',
        'type' => 1
    ),
    'num' =>
    array(
        'title' => '数量',
    ),
    'finish_num' =>
    array(
        'title' => '完成数量',
    ),
    'spec1_name' =>
    array(
        'title' => '颜色',
    ),
    'spec2_name' =>
    array(
        'title' => '尺码',
    ),
    'price' =>
    array(
        'title' => '批发价',
        'type' => 1
    ),
    'price1' =>
    array(
        'title' => '批发单价',
        'type' => 1
    ),
    'money' =>
    array(
        'title' => '金额',
        'type' => 1
    ),
    'remark' =>
    array(
        'title' => '备注',
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
