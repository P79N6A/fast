<?php

$baseinfo = $response['data']['baseinfo'];
$sell_return_code_html = $baseinfo['sell_return_code'];
$sell_return_code_html .= '【'.$baseinfo['return_type_txt'].'】';

$data_sell_record_checkpay_status = array(array('1','已确认'),array('0','未确认'));

$is_compensate_txt = $baseinfo['is_compensate'] == 0 ? '否' : '是';
render_control('FormTable', 'baseinfo_form', array(
    'conf' => array(
        'fields' => array(
            array('title' => '退单号(类型)', 'type' => 'html','html'=>$sell_return_code_html),
            array('title' => '退单状态', 'type' => 'html', 'html' => $baseinfo['return_order_status_txt']),
            array('title' => '店铺', 'type' => 'label', 'field' => 'shop_name'),
            array('title' => '平台退单号（平台退单状态）', 'type' => 'html', 'html' => ''),                       
            array('title' => '原单号（交易号）', 'type' => 'html', 'html' => $baseinfo['sell_record_code_txt']),
            array('title' => '原单支付方式', 'type' => 'label', 'field' => 'sell_record_pay_name'),
            array('title' => '买家昵称', 'type' => 'label', 'field' => 'buyer_name'),
            array('title' => '原单发货状态', 'type' => 'label', 'field' => 'sell_record_shipping_status_txt'),  
            
            array('title' => '额外赔付', 'type' => 'html', 'html'=>$is_compensate_txt),
            array('title' => '原单收货人', 'type' => 'label', 'field' => 'return_name'),
            array('title' => '退款方式', 'type' => 'select', 'field' => 'return_pay_code', 'data' =>ds_get_select('refund_type')),
            array('title' => '原单收货人手机', 'type' => 'label', 'field' => 'return_mobile'),                       
            array('title' => '退单原因', 'type' => 'select', 'field' => 'return_reason_code', 'data' =>ds_get_select('return_reason')),
            array('title' => '买家确认支付状态', 'type' => 'radio_group', 'field' => 'sell_record_checkpay_status','data'=>$data_sell_record_checkpay_status),
            array('title' => '买家退单说明', 'type' => 'input', 'field' => 'return_buyer_memo'),            
            array('title' => '创建时间', 'type' => 'label', 'field' => 'create_time'),
            array('title' => '卖家退单备注', 'type' => 'textarea', 'field' => 'return_remark'),
            array('title' => '&nbsp;', 'type' => '', 'field' => ''),                         
        ),
    ),
    'col' => 2,
    'buttons' => array(),
    'per' => '0.3',
    'data' => $response['data']['baseinfo'],
));
?>

