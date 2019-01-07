<?php
$list = array(
    //array(
            //    'type' => 'text',
            //    'show' => 1,
            //    'title' => '订单图标',
            //    'field' => 'status_text',
            //    'width' => '70',
            //    'align' => ''
            //),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '退单号',
        'field' => 'sell_return_code',
        'width' => '120',
        'align' => ''
    ),
    array (
        'type' => 'text',
        'show' => 1,
        'title' => '交易号',
        'field' => 'deal_code',
        'width' => '120',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '销售平台',
        'field' => 'sale_channel_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺',
        'field' => 'shop_name',
        'width' => '100',
        'align' => ''
    ),
     array(
        'type' => 'text',
        'show' => 1,
        'title' => '会员昵称',
        'field' => 'buyer_name',
        'width' => '150',
        'align' => ''
    ),
    array(
            'type' => 'text',
            'show' => 1,
            'title' => '退货时间',
            'field' => 'receive_time',
            'width' => '150',
            'align' => ''
    ),
   array(
        'type' => 'text',
        'show' => 1,
        'title' => '仓库',
        'field' => 'store_name',
        'width' => '100',
        'align' => 'center',
    ),
    array (
            'type' => 'text',
            'show' => 1,
            'title' => '配送方式',
            'field' => 'express_name',
            'width' => '100',
            'align' => ''
    ),
    array(
            'type' => 'text',
            'show' => 1,
            'title' => '商品编码',
            'field' => 'goods_code',
            'width' => '100',
            'align' => ''
    ),
    //2017-12-07 task#1907  增加颜色，尺码，季节，分类字段的值
    array(
            'type' => 'text',
            'show' => 1,
            'title' => '商品名称',
            'field' => 'goods_name',
            'width' => '150',
            'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => $response['goods_spec1_rename'],
        'field' => 'spec1_name',
        'width' => '100',
        'align' => ''
     ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => $response['goods_spec2_rename'],
        'field' => 'spec2_name',
        'width' => '100',
        'align' => ''
     ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '季节',
        'field' => 'season_name',
        'width' => '100',
        'align' => ''
     ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '分类',
        'field' => 'category_name',
        'width' => '100',
        'align' => ''
     ),
    array(
            'type' => 'text',
            'show' => 1,
            'title' => '商品条形码',
            'field' => 'barcode',
            'width' => '130',
            'align' => ''
    ),            
     array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品数量',
        'field' => 'recv_num',
        'width' => '100',
      //  'align' => 'center',
    ),
    array(
            'type' => 'text',
            'show' => 1,
            'title' => '商品销售额',
            'field' => 'avg_money',
            'width' => '100',
            'align' => ''
    ),
    array(
            'type' => 'text',
            'show' => 1,
            'title' => '商品成本',
            'field' => 'goods_cost_price',
            'width' => '100',
            'align' => ''
    ),
    array(
            'type' => 'text',
            'show' => 1,
            'title' => '商品毛利',
            'field' => 'goods_gross_profit',
            'width' => '100',
            'align' => ''
    ),
    array(
            'type' => 'text',
            'show' => 1,
            'title' => '商品毛利率',
            'field' => 'goods_gross_profit_rate',
            'width' => '100',
            'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '吊牌价格',
        'field' => 'goods_price',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '理论重量(Kg)',
        'field' => 'weight',
        'width' => '100',
        'align' => ''
     ),
);
//2017-12-07 task#1907  增加自定义列设置功能
if(!empty($response['proprety'])) {
        foreach($response['proprety'] as $val) {
            $list[] = array('title' => $val['property_val_title'],
                'show' => 1,
                'type' => 'text',
                'width' => '80',
                'field' => $val['property_val']);
        }
} 
    render_control('DataTable', 'sell_record_and_return_table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'oms/SellGoodsProfitRateModel::get_sell_by_page',
  //  'queryBy' => 'searchForm',
    'init' => 'nodata',
    'customFieldTable' => 'rpt/sell_record_and_return_table',
    'idField' => 'sell_record_code',
    'export'=> array('id'=>'exprot_sell_record_and_return','conf'=>'sell_goods_profit_rate_sell_record_and_return','name'=>'退货商品毛利分析','export_type'=>'file'),
) );
?>
