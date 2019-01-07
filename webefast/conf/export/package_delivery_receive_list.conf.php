<?php
$spec1_name=load_model("sys/SysParamsModel")->get_val_by_code('goods_spec1');
$spec2_name=load_model("sys/SysParamsModel")->get_val_by_code('goods_spec2');
return array (
  'shop_name' =>
  array (
      'title' => '店铺',
  ),
  'sale_channel_name' => 
  array (
      'title' => '平台',
  ),
  'deal_code_list' => 
  array (
      'title' => '交易号','is_sensitive'=>array('oms_sell_record','deal_code_list'),
  ),
  'sell_record_code' => 
  array (
      'title' => '订单号','type'=>1
  ),
  'buyer_name' => 
  array (
      'title' => '买家昵称','is_sensitive'=>array('oms_sell_record','buyer_name')
  ),
  'receiver_name' => 
  array (
      'title' => '收货人','is_sensitive'=>array('oms_sell_record','receiver_name'),'type'=>8
  ),
  'receiver_mobile' => 
  array (
      'title' => '手机','is_sensitive'=>array('oms_sell_record','receiver_mobile'),
  ),
  'receiver_address' => 
  array (
      'title' => '收货地址','is_sensitive'=>array('oms_sell_record','receiver_address'),
  ),
  'store_name' =>
  array (
      'title' => '仓库',
  ),
  'express_name' =>
  array (
      'title' => '配送方式',
  ),
  'express_no' => 
  array (
      'title' => '快递单号','type'=>1
  ),
  'delivery_time' =>
  array(
      'title' => '发货时间','type' => 1,
  ),
  'goods_num' =>
  array(
      'title' => '商品总数量', 'type' => 1
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
  'spec1_name' =>
  array(
        'title' => $spec1_name['goods_spec1'], 'type' => 1),
  'spec2_name' =>
  array(
      'title' => $spec2_name['goods_spec2'], 'type' => 1
  ),
  'num' =>
  array(
      'title' => '数量',
  ),
  'goods_price' =>
  array(
      'title' => '吊牌价',
  ),
  'avg_money' =>
  array(
      'title' => '均摊金额',
  ),
) ;