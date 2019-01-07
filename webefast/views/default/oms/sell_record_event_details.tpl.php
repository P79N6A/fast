<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
            	'type' => 'text',
            	'show' => 1,
            	'title' => '优惠描述',
            	'field' => 'promotion_name',
            	'width' => '200',
            	'sortable' => true
            ),
            array (
            	'type' => 'text',
            	'show' => 1,
            	'title' => '优惠价格',
            	'field' => 'discount_fee',
            	'width' => '120',
            	'sortable' => true
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '优惠商品',
                'field' => 'goods_name',
                'width' => '420',
                'align' => '',
                'sortable' => true
            ),
        )
    ),
    'dataset' => 'oms/SellRecordModel::get_event_details',
    'idField' => 'return_record_detail_id',
    'params' => array('filter' => array('deal_code_list' => $request['deal_code_list'],'sale_channel_code' => $request['sale_channel_code'])),
));
?>

