<?php

$export_list = array(
    'return_notice_code' =>
    array(
        'title' => '单据编号', 'type' => 1
    ),
    'is_check_name' =>
    array(
        'title' => '确认状态', 'type' => 1
    ),
    'is_return_name' =>
    array(
        'title' => '生成退货单状态', 'type' => 1
    ),
    'is_finish_name' =>
    array(
        'title' => '完成状态', 'type' => 1
    ),
    'order_time' =>
    array(
        'title' => '下单时间', 'type' => 1
    ),
    'custom_code_name' =>
    array(
        'title' => '分销商', 'type' => 1
    ),
    'store_code_name' =>
    array(
        'title' => '仓库', 'type' => 1
    ),
    'num' =>
    array(
        'title' => '总数量',
    ),
    'finish_num' =>
    array(
        'title' => '总完成数量',
    ),
    'money' =>
    array(
        'title' => '总金额',
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
    'detail_trade_price' =>
    array(
        'title' => '批发价',
    ),
    'detail_price' =>
    array(
        'title' => '批发单价',
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

