<?php
render_control ( 'DataTable', 'sale_channel_table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'sale_channel_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品应收总额',
                'field' => 'goods_money',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '邮费',
                'field' => 'express_money',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发货数量',
                'field' => 'goods_count',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单量',
                'field' => 'record_count',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'rpt/SellRecordReportModel::shipped_sale_channel',
    //'queryBy' => 'searchForm',
    'idField' => 'sale_channel_code',
    //'RowNumber'=>true,
    //'CascadeTable' => array(),
    //'CheckSelection'=>true,
    'init' => 'nodata',
    //'init_note_nodata' => '点击查询显示数据',
    'events' => array(
        //'rowdblclick' => 'showDetail',
    ),
    'export'=> array('id'=>'exprot_shipped_sale_channel','conf'=>'sell_record_shipped_sale_channel','name'=>'订单发货数据分析','export_type'=>'file'),
) );