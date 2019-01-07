<?php
render_control('DataTable', 'statistic_list_table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '月份',
                'field' => 'add_month',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '新增会员数',
                'field' => 'new_custom_num',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '新会员消费金额',
                'field' => 'new_consume_money',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '老会员数',
                'field' => 'old_custom_num',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '老会员消费金额',
                'field' => 'old_consume_money',
                'width' => '150',
                'align' => ''
            )
        )
    ),
    'dataset' => 'crm/CustomerModel::get_improve_by_filter',
    //  'queryBy' => 'searchForm',
    // 'export' => array('id' => 'exprot_list', 'conf' => 'rpt_sell_report_data_analyse_list', 'name' => '销售数据分析', 'export_type' => 'file'),
    //'idField' => 'sell_record_code',
    'idField' => 'customer_id',
    'init' => 'nodata',
    'events' => array(),
));
?>
