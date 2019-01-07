<?php

render_control('FormTable', 'return_order_form', array(
    'conf' => array(
        'fields' => array(
            array('title' => '换货单号', 'type' => 'label', 'field' => 'change_record'),
            array('title' => '退货仓库', 'type' => 'select', 'field' => 'store_code', 'data' => load_model('base/StoreModel')->get_select()),
            array('title' => '退单原因', 'type' => 'select', 'field' => 'return_reason_code', 'data' => ds_get_select('return_reason')),
            array('title' => '退款方式', 'type' => 'select', 'field' => 'return_pay_code', 'data' => ds_get_select('refund_type')),
            array('title' => '买家退单说明', 'type' => 'input', 'field' => 'return_buyer_memo'),
            array('title' => '退单处理客服', 'type' => 'input', 'field' => 'service_code'),
            array('title' => '卖家退单备注', 'type' => 'input', 'field' => 'return_remark'),
            array('title' => '业务日期', 'type' => 'date', 'field' => 'stock_date'),
        ),
        'hidden_fields' => array(
            array('field' => 'sell_return_code', 'value' => $response['data']['sell_return_code']),
        ),
    ),
    'act_edit' => 'oms/sell_return/do_edit&app_fmt=json',
    'col' => 2,
    'per' => '0.3',
    'buttons' => array(),
    'data' => $response['data']['return_order'],
));
?>

