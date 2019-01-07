<?php

render_control('DataTable', 'shop_table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货数量',
                'field' => 'return_num',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退款金额',
                'field' => 'return_money',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '赔付金额',
                'field' => 'compensate_money',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '卖家承担运费',
                'field' => 'seller_express_money',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手工调整金额',
                'field' => 'adjust_money',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'rpt/SellReturnReportModel::get_shop_analusis',
    // 'queryBy' => 'searchForm',
    'idField' => 'shop_code',
    //'RowNumber'=>true,
    'init' => 'nodata',
    //'init_note_nodata' => '点击查询显示数据',
    'events' => array(
    //'rowdblclick' => 'showDetail',
    ),
));