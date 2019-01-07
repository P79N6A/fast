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
                'field' => 'action_time',
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
                'title' => '发货状态',
                'field' => 'send_status',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'action_desc',
                'width' => '350',
                'align' => ''
            ),
        ) 
    ),
    'dataset' => 'oms_shop/OmsShopModel::get_log_by_page',
    'params' => array('filter' => array('record_code'=>$response['record']['record_code'],'page_size'=>10)),
    'idField' => 'oms_shop_sell_record_log',
    'itemStatusFields' => '{read:"readed"}',
) );
?>