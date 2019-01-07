<?php

return array(
    'oms_sell_record' => array(
        'key' => 'sell_record_code',
        'key_id' => 'sell_record_id',
        'other_key' => array('deal_code_list'),
        'type' => 'move',
        'condition' =>
        array(
            array('time_key' => 'delivery_date', 'type' => 'date',
                'where' => " order_status=1 AND shipping_status=4 ",
            ),
            array('time_key' => 'lastchanged', 'type' => 'datetime',
                    'where' => " order_status=3 ",
            ),
        ),
        'move' => array(
            'oms_sell_record_detail' => array('key' => 'sell_record_code',),
            'oms_sell_record_lof' => array('key' => 'record_code', "condition" => array('where' => "record_type = 1 ")),
            'oms_sell_record_action' =>  array('key' => 'sell_record_code',),
            'oms_sell_record_cz' =>  array('key' => 'sell_record_code',),
            'oms_sell_settlement_record' => array('key' => 'sell_record_code', 'condition' => array('where' => 'order_attr = 1')),
            'oms_sell_settlement_detail' => array('key' => 'sell_record_code', 'condition' => array('where' => 'order_attr = 1')),
            'oms_sell_settlement' => array('key' => array('deal_code_list', 'deal_code'), 'key_type' => 'explode', 'condition' => array('where' => " order_attr IN (1,2) "),
            ),
        ),
        'index' => array(
            'oms_deliver_record_package' => array(
                'key' => 'sell_record_code',
                'index_type' => 'express',
                'index_val' => 'express_no',
                 'condition' => array('where' => " express_no<>'' ")
            ),
        ),
        'del' => array(
            'oms_sell_record_tag' =>  array('key' => 'sell_record_code',),
            'api_order' => array(
                'key' => array('deal_code_list', 'tid'),
                'key_type' => 'explode',
            ),
            'api_order_detail' => array(
                'key' => array('deal_code_list', 'tid'),
                'key_type' => 'explode',
            ),
            'api_refund' => array(
                'key' => array('deal_code_list', 'tid'),
                'key_type' => 'explode',
            ),
            'api_refund_detail' => array(
                'key' => array('deal_code_list', 'tid'),
                'key_type' => 'explode',
            ),
            'api_order_send' =>  array('key' => 'sell_record_code',),
            'op_strategy_log' => array('key' => 'sell_record_code',),
            'wms_oms_trade' =>
            array('key' => 'record_code',
                'condition' => array('where' => " record_type='sell_record' "),
            ),
            'wms_oms_order' => array('key' => 'record_code',
                'condition' => array('where' => " record_type='sell_record' ")
            ),
            'goods_inv_record' => array('key' => 'relation_code',
                'condition' => array('where' => " relation_type ='oms' ")
            ),
        ),
    ),
    'oms_sell_return' => array(
        'key' => 'sell_return_code',
        'key_id' => 'sell_return_id',
        'type' => 'move',
        'condition' =>
        array(
            array('time_key' => 'receive_time', 'type' => 'datetime',
                'where' => ' return_order_status =1 AND return_shipping_status=1 AND  return_type>1 ',
            ),
        ),
        'move' => array(
            'oms_sell_return_detail' => array(),
            'oms_sell_return_action' => array(),
            'oms_sell_settlement_record' => array('key' => 'sell_record_code', 'condition' => array('where' => 'order_attr = 2 ')),
            'oms_sell_settlement_detail' => array('key' => 'sell_record_code', 'condition' => array('where' => 'order_attr = 2 ')),
        ),
        'del' => array(
            'oms_sell_return_tag' => array(
            ),
            'wms_oms_trade' => array('key' => 'record_code', 'condition' => array('where' => " record_type='sell_return' ")),
            'wms_oms_order' => array('key' => 'record_code', 'condition' => array('where' => " record_type='sell_return' ")),
        ),
    ),
    'oms_deliver_record' => array(
        'key' => 'sell_record_code',
        'type' => 'del',
        'condition' =>
        array(
            array('time_key' => 'delivery_date', 'type' => 'date', 'where' => ' is_cancel=0 '
            ),
            array('time_key' => 'lastchanged', 'type' => 'datetime', 'where' => 'is_cancel>0'
            ),
        ),
        'del' => array(
            'oms_deliver_record_detail' =>  array('key' => 'sell_record_code',),
        ),
    ),
    'oms_waves_record' => array(
        'type' => 'del',
        'condition' =>
        array(
            array('time_key' => 'accept_time', 'type' => 'date_time',
                'where' => 'is_accept=1 AND   0=(select count(1) from oms_deliver_record where oms_deliver_record.waves_record_id=oms_waves_record.waves_record_id)',
            ),
            array('time_key' => 'cancel_time', 'type' => 'datetime', 'where' => 'is_cancel>0'
            ),
        ),
        'parent'=>'oms_deliver_record',
    ),
    'oms_return_package' => array(
        'key' => 'return_package_code',
        'key_id' => 'return_package_id',
        'type' => 'move',
        'condition' =>
        array(
            array('time_key' => 'stock_date', 'type' => 'date',
                'where' => 'return_order_status=1 ',
            ),
            
            array('time_key' => 'lastchanged', 'type' => 'datetime', 'where' => ' return_order_status=2 ',
            ),
        ),
        'move' => array(
            'oms_return_package_detail' => array(),
            'oms_sell_record_lof' => array(
                'key' => 'record_code',
                "condition" => array('where' => 'record_type = 2')),
        ),
        'del' => array(
            'oms_return_package_action' => array(
            ),
            'goods_inv_record' => array('key' => 'relation_code',
                'condition' => array('where' => "relation_type = 'oms_return'"),
            ),
        ),
    ),
);
