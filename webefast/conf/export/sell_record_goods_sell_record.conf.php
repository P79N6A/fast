<?php

$ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
$property_power = $ret_cfg['property_power'];
if ($property_power) {
    $property_data = load_model('prm/GoodsPropertyModel')->get_property_val('property_val_title,property_val');
}
$goods_export = array(
    'sell_record_code' =>
    array(
        'title' => '订单号', 'type' => 1
    ),
    'deal_code' =>
    array(
        'title' => '交易号', 'type' => 1
    ),
    'pay_name' =>
    array(
        'title' => '支付方式',
    ),
    'buyer_name' =>
    array(
        'title' => '会员',
    ),
    'sale_channel_name' =>
    array(
        'title' => '销售平台',
    ),
    'shop_name' =>
    array(
        'title' => '店铺',
    ),
    'record_time' =>
    array(
        'title' => '下单时间',
    ),
    'pay_time' =>
    array(
        'title' => '付款时间',
    ),
    'delivery_time' =>
    array(
        'title' => '发货时间',
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
        'title' => '颜色',
    ),
    'spec2_name' =>
    array(
        'title' => '尺码',
    ),
    'barcode' =>
    array(
        'title' => '条形码', 'type' => 1
    ),
    'store_name' =>
    array(
        'title' => '仓库',
    ),
    'brand_name' =>
    array(
        'title' => '品牌',
    ),
    'season_name' =>
    array(
        'title' => '季节',
    ),
    'category_name' =>
    array(
        'title' => '分类',
    ),
    'avg_money' =>
    array(
        'title' => '销售金额', 'type' => 1
    ),
    'real_count' =>
    array(
        'title' => '实际销售数量', 'type' => 1
    ),
    'num' =>
    array(
        'title' => '销售数量', 'type' => 1
    ),
    'return_num' =>
    array(
        'title' => '退货数量', 'type' => 1
    ),
    'return_money' =>
    array(
        'title' => '退货金额', 'type' => 1
    ),
    'goods_price' =>
    array(
        'title' => '吊牌价格', 'type' => 1
    ),
        );
if ($property_power) {
    foreach ($property_data as $val) {
        $goods_export[$val['property_val']]['title'] = $val['property_val_title'];
    }
}
return $goods_export;



