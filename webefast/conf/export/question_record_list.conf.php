<?php 
return array (
  'sell_record_code' => 
  array (
    'title' => '订单号',
  ),
  'problem_html' => 
  array (
    'title' => '订单问题',
  ),
   'order_remark' => 
  array (
    'title' => '订单备注',
  ),
  'sale_channel_code' => 
  array (
    'title' => '平台',
  ),
  'shop_name' => 
  array (
    'title' => '店铺',
  ),
  'deal_code_list' => 
  array (
    'title' => '交易号','is_sensitive'=>array('oms_sell_record','deal_code_list'),
  ),
		'buyer_name' =>
		array (
				'title' => '买家昵称','is_sensitive'=>array('oms_sell_record','buyer_name'),
		),
  'receiver_name' => 
  array (
    'title' => '收货人','is_sensitive'=>array('oms_sell_record','receiver_name'),
  ),
  'receiver_address' => 
  array (
    'title' => '收货地址','is_sensitive'=>array('oms_sell_record','receiver_address'),
  ),
  'store_name' => 
  array (
    'title' => '仓库',
  ),
  'express_code_name' => 
  array (
    'title' => '配送方式',
  ),
  'record_time' => 
  array (
    'title' => '下单时间',
  ),
  'buyer_remark' => 
  array (
    'title' => '客户留言',
  ),
  'seller_remark' => 
  array (
    'title' => '商家留言',
  ),
		'goods_name' =>
		array (
				'title' => '商品名称',
		),
		'goods_code' =>
		array (
				'title' => '商品编码','type' => 1
		),
		'spec1_code' =>
		array (
				'title' => '规格1','type' => 1
		),
		'spec2_code' =>
		array (
				'title' => '规格2','type' => 1
		),
		'barcode' =>
		array (
				'title' => '商品条形码','type' => 1
		),
		'num' =>
		array (
				'title' => '数量',
		),
		'goods_price' =>
		array (
				'title' => '商品单价',
		),
		'avg_money' =>
		array (
				'title' => '均摊金额',
		),
    'invoice_title' =>
		array (
				'title' => '发票抬头',
		),
    'invoice_content' =>
		array (
				'title' => '发票内容',
		),
) ;