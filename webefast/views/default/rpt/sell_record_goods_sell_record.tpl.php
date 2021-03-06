<?php
$list = array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '130',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code',
                'width' => '130',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '支付方式',
                'field' => 'pay_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '会员',
                'field' => 'buyer_name',
                'width' => '100',
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
                'title' => '下单时间',
                'field' => 'record_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '付款时间',
                'field' => 'pay_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发货时间',
                'field' => 'delivery_time',
                'width' => '150',
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
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '颜色',
                'field' => 'spec1_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '尺码',
                'field' => 'spec2_name',
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
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '品牌',
                'field' => 'brand_name',
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
                'title' => '年份',
                'field' => 'year_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售应收金额',
                'field' => 'avg_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '实际销售数量',
                'field' => 'real_count',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售数量',
                'field' => 'num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货数量',
                'field' => 'return_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货应退金额',
                'field' => 'return_money',
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
        );
if(!empty($response['proprety'])) {
    foreach($response['proprety'] as $val) {
    $list[] = array('title' => $val['property_val_title'],
                'show' => 1,
                'type' => 'text',
                'width' => '80',
                'field' => $val['property_val']);
    }
}
render_control('DataTable', 'sell_record_table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'oms/SellReportModel::get_goods_sell_record',
  // 'queryBy' => 'searchForm',
   // 'export' => array('id' => 'exprot_list', 'conf' => 'rpt_sell_report_data_analyse_list', 'name' => '销售数据分析', 'export_type' => 'file'),
    'init' => 'nodata',
    'idField' => 'sell_record_code',
    'events' => array(

    ),
));
?>