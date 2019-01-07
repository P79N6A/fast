<?php
render_control ( 'DataTable', 'goods_code_table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '160',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售数量',
                'field' => 'num',
                'width' => '130',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售金额',
                'field' => 'avg_money',
                'width' => '130',
                'align' => 'center'
            ),
        )
    ),
    'dataset' => 'rpt/SellGoodsReportModel::trends_goods_code',
    'idField' => 'sell_record_code',
    'init' => 'nodata',
    'events' => array(
        //'rowdblclick' => 'showDetail',
    ),
) );
