<?php

$baseinfo = $response['data']['baseinfo'];
/* 获取与store_code对接的第三方仓库 */
$is_WMS = load_model('sys/ShopStoreModel')->is_wms_store($baseinfo['store_code']);
/* 有为1，无为-1 */
$response['is_wms'] = !empty($is_WMS) ? 1 : -1;
$sell_return_code_html = $baseinfo['sell_return_code'];
$sell_return_code_html .= '【' . $baseinfo['return_type_txt'] . '】';

$data_sell_record_checkpay_status = array(array('1', '已确认'), array('0', '未确认'));


if ($baseinfo['type_confirm'] == 0) {
    render_control('FormTable', 'baseinfo_form', array(
        'conf' => array(
            'fields' => array(
                array('title' => '退单号(类型)', 'type' => 'html', 'html' => $sell_return_code_html),
                array('title' => '退单状态', 'type' => 'html', 'html' => $baseinfo['return_order_status_txt']),
                array('title' => '店铺', 'type' => 'label', 'field' => 'shop_name'),
                array('title' => '平台退单号', 'type' => 'html', 'html' => $baseinfo['refund_id_txt']),
                array('title' => '原单号（交易号）', 'type' => 'html', 'html' => $baseinfo['sell_record_code_txt']),
                array('title' => '原单支付方式', 'type' => 'label', 'field' => 'sell_record_pay_name'),
                array('title' => '退货包裹单号', 'type' => 'label', 'field' => 'sell_return_package_code'),
                array('title' => '原单发货状态', 'type' => 'label', 'field' => 'sell_record_shipping_status_txt'),
                array('title' => '退货仓库', 'type' => 'select', 'field' => 'store_code', 'data' => load_model('base/StoreModel')->get_purview_store()),
                array('title' => '原单快递公司', 'type' => 'label', 'field' => 'sell_record_express_name'),
                array('title' => '买家退货快递公司', 'type' => 'select', 'field' => 'return_express_code', 'data' => ds_get_select('express_company', 2)),
                array('title' => '原单物流单号', 'type' => 'label', 'field' => 'sell_record_express_no'),
                array('title' => '买家退货物流单号', 'type' => 'input', 'field' => 'return_express_no'),
                array('title' => '买家确认支付状态', 'type' => 'radio_group', 'field' => 'sell_record_checkpay_status', 'data' => $data_sell_record_checkpay_status),
                array('title' => '退款方式', 'type' => 'select', 'field' => 'return_pay_code', 'data' => ds_get_select('refund_type',2)),
                array('title' => '创建时间', 'type' => 'label', 'field' => 'create_time'),
                array('title' => '退单原因', 'type' => 'select', 'field' => 'return_reason_code', 'data' => ds_get_select('return_reason')),
                array('title' => '收货时间', 'type' => 'label', 'field' => 'receive_time'),
                array('title' => '买家退单说明', 'type' => 'textarea', 'field' => 'return_buyer_memo'),
                array('title' => '卖家退单备注', 'type' => 'textarea', 'field' => 'return_remark'),
            ),
        ),
        'col' => 2,
        'buttons' => array(),
        'per' => '0.3',
        'data' => $response['data']['baseinfo'],
    ));
} else if ($baseinfo['type_confirm'] == 1 && $response['is_wms'] == -1) {
    render_control('FormTable', 'baseinfo_form', array(
        'conf' => array(
            'fields' => array(
                array('title' => '退单号(类型)', 'type' => 'html', 'html' => $sell_return_code_html),
                array('title' => '退单状态', 'type' => 'html', 'html' => $baseinfo['return_order_status_txt']),
                array('title' => '店铺', 'type' => 'label', 'field' => 'shop_name'),
                array('title' => '平台退单号', 'type' => 'html', 'html' => $baseinfo['refund_id_txt']),
                array('title' => '原单号（交易号）', 'type' => 'html', 'html' => $baseinfo['sell_record_code_txt']),
                array('title' => '原单支付方式', 'type' => 'label', 'field' => 'sell_record_pay_name'),
                array('title' => '退货包裹单号', 'type' => 'label', 'field' => 'sell_return_package_code'),
                array('title' => '原单发货状态', 'type' => 'label', 'field' => 'sell_record_shipping_status_txt'),
                array('title' => '退货仓库', 'type' => 'select', 'field' => 'store_code', 'data' => load_model('base/StoreModel')->get_purview_store()),
                array('title' => '原单快递公司', 'type' => 'label', 'field' => 'sell_record_express_name'),
                array('title' => '买家退货快递公司', 'type' => 'select', 'field' => 'return_express_code', 'data' => ds_get_select('express_company', 2)),
                array('title' => '原单物流单号', 'type' => 'label', 'field' => 'sell_record_express_no'),
                array('title' => '买家退货物流单号', 'type' => 'input', 'field' => 'return_express_no'),
                array('title' => '买家确认支付状态', 'type' => 'label', 'field' => 'sell_record_checkpay_status_name'),
                array('title' => '退款方式', 'type' => 'label', 'field' => 'return_pay_name'),
                array('title' => '创建时间', 'type' => 'label', 'field' => 'create_time'),
                array('title' => '退单原因', 'type' => 'select', 'field' => 'return_reason_code', 'data' => ds_get_select('return_reason')),
                array('title' => '收货时间', 'type' => 'label', 'field' => 'receive_time'),
                array('title' => '买家退单说明', 'type' => 'label', 'field' => 'return_buyer_memo'),
                array('title' => '卖家退单备注', 'type' => 'textarea', 'field' => 'return_remark'),
            ),
        ),
        'col' => 2,
        'buttons' => array(),
        'per' => '0.3',
        'data' => $response['data']['baseinfo'],
    ));
} else {
    render_control('FormTable', 'baseinfo_form', array(
        'conf' => array(
            'fields' => array(
                array('title' => '退单号(类型)', 'type' => 'html', 'html' => $sell_return_code_html),
                array('title' => '退单状态', 'type' => 'html', 'html' => $baseinfo['return_order_status_txt']),
                array('title' => '店铺', 'type' => 'label', 'field' => 'shop_name'),
                array('title' => '平台退单号', 'type' => 'html', 'html' => $baseinfo['refund_id_txt']),
                array('title' => '原单号（交易号）', 'type' => 'html', 'html' => $baseinfo['sell_record_code_txt']),
                array('title' => '原单支付方式', 'type' => 'label', 'field' => 'sell_record_pay_name'),
                array('title' => '退货包裹单号', 'type' => 'label', 'field' => 'sell_return_package_code'),
                array('title' => '原单发货状态', 'type' => 'label', 'field' => 'sell_record_shipping_status_txt'),
                array('title' => '退货仓库', 'type' => 'label', 'field' => 'store_code_name',),
                array('title' => '原单快递公司', 'type' => 'label', 'field' => 'sell_record_express_name'),
                array('title' => '买家退货快递公司', 'type' => 'select', 'field' => 'return_express_code', 'data' => ds_get_select('express_company', 2)),
                array('title' => '原单物流单号', 'type' => 'label', 'field' => 'sell_record_express_no'),
                array('title' => '买家退货物流单号', 'type' => 'input', 'field' => 'return_express_no'),
                array('title' => '买家确认支付状态', 'type' => 'label', 'field' => 'sell_record_checkpay_status_name'),
                array('title' => '退款方式', 'type' => 'label', 'field' => 'return_pay_name'),
                array('title' => '创建时间', 'type' => 'label', 'field' => 'create_time'),
                array('title' => '退单原因', 'type' => 'label', 'field' => 'return_reason_name'),
                array('title' => '收货时间', 'type' => 'label', 'field' => 'receive_time'),
                array('title' => '买家退单说明', 'type' => 'label', 'field' => 'return_buyer_memo'),
                array('title' => '卖家退单备注', 'type' => 'textarea', 'field' => 'return_remark'),
            ),
        ),
        'col' => 2,
        'buttons' => array(),
        'per' => '0.3',
        'data' => $response['data']['baseinfo'],
    ));
}
?>

