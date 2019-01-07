<?php
render_control('DataTable', 'record_table', array(
    'conf' => array(
        'list' => array(
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
                'width' => '150',
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
                'title' => '订单编号',
                'field' => 'sell_record_code',
                'width' => '130',
                'align' => ''
            ),
           array(
                'type' => 'text',
                'show' => 1,
                'title' => '会员昵称',
                'field' => 'buyer_name',
                'width' => '100',
                'align' => ''
            ),  
           array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售金额',
                'field' => 'avg_money',
                'width' => '100',
                'align' => ''
            ),  
           array(
                'type' => 'text',
                'show' => 1,
                'title' => '邮费',
                'field' => 'express_money',
                'width' => '100',
                'align' => ''
            ),  
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单总金额',
                'field' => 'order_money',
                'width' => '100',
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
                'title' => '订单备注',
                'field' => 'seller_remark',
                'width' => '100',
                'align' => ''
            ),  
        )
    ),
    'dataset' => 'oms/SellReportModel::get_record_data',
   // 'queryBy' => 'searchForm',
   // 'export' => array('id' => 'exprot_list', 'conf' => 'rpt_sell_report_data_analyse_list', 'name' => '销售数据分析', 'export_type' => 'file'),
    'init' => 'nodata',
    'idField' => 'sell_record_code',
));
?>