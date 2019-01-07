<?php

require_lib('util/web_util', true);

class work_bench {

    //千牛展示页面
    function do_list(array &$request, array &$response, array &$app) {
        if (!isset($request['kh_id']) || empty($request['kh_id']) || !isset($request['ticket_id']) || empty($request['ticket_id'])) {
            $response['message'] = "请求数据异常！";
            return false;
        }

        load_model('api/ApiKehuModel')->change_db_conn($request['kh_id']);

        $ret = load_model('qianniu/TicketModel')->get_ticket($request['ticket_id']);
        if (empty($ret['data'])) {
            $response['message'] = "请求数据异常！";
            return false;
        }


        if (empty($ret['data']['record_code'])) {
            $response['message'] = "单据等待处理，请耐心等待...";
            return false;
        }

        $sell_return_code = $ret['data']['record_code'];

        $sql = "select sell_record_code,return_type,buyer_name,sell_return_code,return_order_status,finance_check_status,return_shipping_status,deal_code,return_express_code,return_express_no,return_buyer_memo,refund_total_fee from oms_sell_return where sell_return_code = :sell_return_code";
        $arr = CTX()->db->get_row($sql, array(':sell_return_code' => $sell_return_code));
        $express_name = '';
        if (!empty($arr['return_express_code'])) {
            $sql = "select express_name from base_express where express_code = :express_code";
            $express_name = CTX()->db->get_value($sql, array(':express_code' => $arr['return_express_code']));
        }

        $sql = "select buyer_alipay_no from oms_sell_record where sell_record_code = :sell_record_code";
        $buyer_ali_pay_no = CTX()->db->get_row($sql, array(':sell_record_code' => $arr['sell_record_code']));

        if ($arr['return_type'] == 1) {
            $return_name = '仅退款';
        } else if ($arr['return_type'] == 2) {
            $return_name = '仅退货';
        } else {
            $return_name = '退款退货';
        }

        $return_order_name = $arr['return_order_status'] == 0 ? '未确认' : '确认';
        $return_shipping_name = $arr['return_shipping_status'] == 0 ? '未收货' : '收货';
        $finance_check_name = $arr['finance_check_status'] == 0 ? '未退款' : '退款';

        $response['buyer_nick'] = $arr['buyer_name'];
        $response['sell_return_code'] = $arr['sell_return_code'] . '（' . $return_name . '）';
        $response['return_status'] = $return_order_name . ' ' . $return_shipping_name . ' ' . $finance_check_name;
        $response['deal_code'] = $arr['deal_code'];
        $response['return_express_name'] = '';
        if (!empty($express_name)) {
            $response['return_express_name'] = $express_name . '（' . $arr['return_express_no'] . '）';
        }

        $response['return_buyer_memo'] = $arr['return_buyer_memo'];
        $response['buyer_ali_pay_no'] = $buyer_ali_pay_no['buyer_alipay_no'];
        $response['refund_total_fee'] = $arr['refund_total_fee'];
    }

}
