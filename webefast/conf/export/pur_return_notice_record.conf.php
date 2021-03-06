<?php

$export_list = array(
    'record_code' =>
    array(
        'title' => '单据编号',
    ),
    'is_sure_name' =>
    array(
        'title' => '确认状态', 'type' => 1
    ),
    'is_execute_name' =>
    array(
        'title' => '生成退货单状态', 'type' => 1
    ),
    'is_stop_name' =>
    array(
        'title' => '终止状态', 'type' => 1
    ),
    'order_time' =>
    array(
        'title' => '下单时间', 'type' => 1
    ),
    'record_time' =>
    array(
        'title' => '业务日期', 'type' => 1
    ),
    'supplier_code_name' =>
    array(
        'title' => '供应商',
    ),
    'store_code_name' =>
    array(
        'title' => '仓库',
    ),
    'num' =>
    array(
        'title' => '总数量',
    ),
    'finish_num' =>
    array(
        'title' => '总完成数量',
    ),
    'remark' =>
    array(
        'title' => '备注', 'type' => 1
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
    'detail_num' =>
    array(
        'title' => '数量',
    ),
    'detail_finish_num' =>
    array(
        'title' => '完成数量',
    ),
    'detail_money' =>
    array(
        'title' => '金额',
    ),
    'price' =>
    array(
        'title' => '标准进价',
    ),
    'price1' =>
    array(
        'title' => '单价',
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
