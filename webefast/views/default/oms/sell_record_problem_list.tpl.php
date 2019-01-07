<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单类型',
                'field' => 'order_type',
                'width' => '100',
                'align' => '',
                'format_js' => array('type'=>'html','value'=>$response['is_handwork']>0?'手工单':'系统转单')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '问题类型',
                'field' => 'tag_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '问题备注',
                'field' => 'tag_desc',
                'width' => '200',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/SellRecordTagModel::get_list_by_code',
//    'queryBy' => 'searchForm',
    'idField' => 'sell_record_code',
    'params' =>array(
        'filter'=>array(
            'sell_record_code' => $response['sell_record_code'],
            'tag_type' => 'problem'
        )
    )
));
?>

