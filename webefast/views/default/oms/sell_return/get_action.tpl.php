
<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作者',
                'field' => 'user_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作名称',
                'field' => 'action_name',
                'width' => '130',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作时间',
                'field' => 'create_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单状态',
                'field' => 'return_order_status',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '物流状态',
                'field' => 'return_shipping_status',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '财务状态',
                'field' => 'finance_check_status',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'action_note',
                'width' => '320',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/SellReturnActionModel::get_by_page',
    'params' => array('filter' => array('sell_return_code' => $request['sell_return_code'], 'page_size' => 10)),
    'idField' => 'sell_return_action_id',
    'itemStatusFields' => '{read:"readed"}',
));
?>