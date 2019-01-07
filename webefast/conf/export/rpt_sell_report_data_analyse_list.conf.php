<?php
$ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
$property_power = $ret_cfg['property_power'];
if($property_power){
  $property_data = load_model('prm/GoodsPropertyModel')->get_property_val('property_val_title,property_val');
}
$export_list = array(
    'sell_record_code' =>
        array(
            'title' => '订单号', 'type' => '1',
        ),
    'deal_code' =>
        array(
            'title' => '交易号', 'type' => '1',
        ),
    'pay_name' =>
        array(
            'title' => '支付方式',
        ),
    'alipay_no' =>
        array(
            'title' => '支付宝交易号',
            'type' => 1
        ),

    'buyer_name' =>
        array(
            'title' => '会员',
        ),
    'sale_channel_code' =>
        array(
            'title' => '销售平台',
        ),
    'express_name' =>
        array(
            'title' => '配送方式',
        ),
    'express_no' =>
        array(
            'title' => '快递单号', 'type' => 1,
        ),
    'receiver_name' =>
        array(
            'title' => '收货人', 'type' => 8
        ),
    'shop_name' =>
        array(
            'title' => '店铺',
        ),
    'record_time' =>
        array(
            'title' => '下单时间', 'type' => '1',
        ),
    'pay_time' =>
        array(
            'title' => '付款时间', 'type' => '1',
        ),
    'delivery_time' =>
        array(
            'title' => '发货时间', 'type' => '1',
        ),
    'goods_code' =>
        array(
            'title' => '商品编码', 'type' => '1',
        ),
    'goods_name' =>
        array(
            'title' => '商品名称',
        ),
    'spec1_name' =>
        array(
            'title' => '颜色',
        ),
    'spec2_name' =>
        array(
            'title' => '尺码',
        ),
    'year_name' =>
        array(
            'title' => '年份',
        ),
    'barcode' =>
        array(
            'title' => '商品条形码', 'type' => '1',
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
    'sell_price' =>
        array(
            'title' => '吊牌价',
        ),
    'cost_price' =>
        array(
            'title' => '成本价',
        ),
    'fx_amount' =>
        array(
            'title' => '销售结算金额',
        ),
    'avg_money' =>
        array(
            'title' => '销售应收金额',
        ),
    'express_money' =>
        array(
            'title' => '邮费',
        ),

    'num' =>
        array(
            'title' => '销售数量',
        ),
    'return_num' =>
        array(
            'title' => '退货数量',
        ),

    'return_money' =>
        array(
            'title' => '退货应退金额',
        ),
    'province_name' =>
        array(
            'title' => '省',
    ),
    'city_name' =>
        array(
            'title' => '市',
    ),
    'receiver_address' =>
        array(
            'title' => '收货人地址',
        ),
);
if($property_power) {
  foreach ($property_data as $val) {
    $export_list[$val['property_val']]['title'] = $val['property_val_title'];
  }
}
return $export_list;
