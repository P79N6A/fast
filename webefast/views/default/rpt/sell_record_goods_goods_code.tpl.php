<?php
$list = array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '190',
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
                'title' => '销售应收金额',
                'field' => 'all_goods_money',
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
                'field' => 'goods_count',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货数量',
                'field' => 'return_count_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货应退金额',
                'field' => 'return_money_all',
                'width' => '100',
                'align' => ''
            )
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
render_control('DataTable', 'goods_code_table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'oms/SellReportModel::get_goods_goods_code_data',
   // 'queryBy' => 'searchForm',
   // 'export' => array('id' => 'exprot_list', 'conf' => 'rpt_sell_report_data_analyse_list', 'name' => '销售数据分析', 'export_type' => 'file'),
    'init' => 'nodata',
    'idField' => 'sell_record_code',
   'events' => array(

    ),
));
?>
