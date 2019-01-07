<?php

$export_list = array(
    'record_code' =>
    array(
        'title' => '单据编号',
    ),
    'init_code' =>
    array(
        'title' => '原单号'
    ),
    'num' =>
    array(
        'title' => '总数'
    ),
    'money' =>
    array(
        'title' => '总金额'
    ),
    'relation_code' =>
    array(
        'title' => '采购订单号',
    ),
    'is_check_name' =>
    array(
        'title' => '确认状态',
    ),
    'is_execute_name' =>
    array(
        'title' => '是否生成入库单',
    ),
    'is_finish_name' =>
    array(
        'title' => '完成状态',
    ),
    'supplier_code_name' =>
    array(
        'title' => '供货商',
    ),
    'store_code_name' =>
    array(
        'title' => '仓库',
    ),
    'record_type' =>
    array(
        'title' => '采购类型',
    ),
    'order_time' => array(
        'title' => '下单日期', 'type' => 1,
    ),
    'goods_name' =>
    array(
        'title' => '商品名称', 'type' => 1
    ),
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => 1
    ),
    'spec1_code' =>
    array(
        'title' => '规格1代码', 'type' => 1
    ),
    'spec1_name' =>
    array(
        'title' => '规格1名称', 'type' => 1
    ),
    'spec2_code' =>
    array(
        'title' => '规格2代码', 'type' => 1
    ),
    'spec2_name' =>
    array(
        'title' => '规格2名称', 'type' => 1
    ),
    'barcode' =>
    array(
        'title' => '商品条形码', 'type' => 1
    ),
    'price' =>
    array(
        'title' => '进货价',
    ),
    'price1' =>
    array(
        'title' => '进货单价',
    ),
    'num_detail' =>
    array(
        'title' => '数量',
    ),
    'price_detail' =>
    array(
        'title' => '金额',
    ),
    'finish_detail' =>
    array(
        'title' => '完成数',
    ),
    'different_num_detail' =>
    array(
        'title' => '差异数',
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
