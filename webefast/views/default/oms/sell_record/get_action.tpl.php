<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '操作者',
                'field' => 'user_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '操作名称',
                'field' => 'action_name',
                'width' => '130',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '操作时间',
                'field' => 'lastchanged',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '付款状态',
                'field' => 'pay_status',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '订单状态',
                'field' => 'order_status',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '发货状态',
                'field' => 'shipping_status',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'action_note',
                'width' => '350',
                'align' => ''
            ),
        ) 
    ),
    'dataset' => 'oms/SellRecordActionModel::get_by_page',
    'params' => array('filter' => array('sell_record_code' => $response['record']['sell_record_code'], 'page_size' => 10)),
    'idField' => 'sell_record_action_id',
    'itemStatusFields' => '{read:"readed"}',
    'scroll_self' => false //控制是否显示拖动图标
));
?>
