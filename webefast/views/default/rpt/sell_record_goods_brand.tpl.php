<?php
render_control('DataTable', 'brand_table', array(
    'conf' => array(
        'list' => array(
           
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
            ),
   
        )
    ),
    'dataset' => 'oms/SellReportModel::get_goods_brand_data',
   // 'queryBy' => 'searchForm',
   // 'export' => array('id' => 'exprot_list', 'conf' => 'rpt_sell_report_data_analyse_list', 'name' => '销售数据分析', 'export_type' => 'file'),
  //  'init' => 'nodata',
    'idField' => 'brand',
    'init' => 'nodata',
    'events' => array(

    ),
));
?>
