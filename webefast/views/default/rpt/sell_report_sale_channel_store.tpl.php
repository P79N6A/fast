<?php
render_control('DataTable', 'sale_channel_store_table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'sale_channel_name',
                'width' => '150',
                'align' => ''
            ),   
           array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售金额',
                'field' => 'all_goods_money',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '邮费',
                'field' => 'express_money_all',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单总金额',
                'field' => 'order_money_all',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/SellReportModel::get_sale_channel_store_data',
   // 'queryBy' => 'searchForm',
   // 'export' => array('id' => 'exprot_list', 'conf' => 'rpt_sell_report_data_analyse_list', 'name' => '销售数据分析', 'export_type' => 'file'),
    'init' => 'nodata',
    'idField' => 'sell_record_code',
));
?>
