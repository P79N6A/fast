
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '交易号',
            'type' => 'input',
            'id' => 'deal_code'
        ),
        array(
            'label' => '买家昵称',
            'type' => 'input',
            'id' => 'buyer_name'
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
            array('type' => 'text',
                'show' => 1,
                'title' => '退单号',
                'field' => 'sell_return_code',
                'width' => '200',
                'align' => ''
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '关联订单号',
                'field' => 'sell_record_code',
                'width' => '200',
                'align' => ''
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '关联交易号',
                'field' => 'deal_code',
                'width' => '200',
                'align' => ''
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '150',
                'align' => ''
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'return_order_status',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array(
                    array('id' => 'select', 'title' => '选择',
                        'act' => '', 'show_name' => '选择'),
                ),
            )
        )
    ),
    'dataset' => 'oms/SellRecordModel::get_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
));
?>
<?php echo_selectwindow_js($request, 'table', array('id'=>'sell_return_code', 'code'=>'sell_record_code', 'name'=>'deal_code')) ?>

