<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class api_taobao_fx_refund {
    //淘宝分销列表
    function do_list(array &$request, array &$response, array &$app) {
        $response['change_fail_num'] = load_model('api/FxTaobaoRefundModel')->get_fail_order_num();
    }
    
    function td_tran(array &$request, array &$response, array &$app) {
        $response = load_model('oms/TranslateRefundModel')->translate_fx_refund($request['sub_order_id']);
        if ($response['status'] == -1) {
            $response['change_fail_num'] = load_model('api/FxTaobaoRefundModel')->get_fail_order_num();
        } else {
            $response['change_fail_num'] = 0;
        }
    }
    function td_traned(array &$request, array &$response, array &$app) {
        
           $response = load_model('api/FxTaobaoRefundModel')->td_traned($request['ids']);
    }
    //单个订单设置为已处理
    function td_traned_one(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $is_change = isset($request['is_change'])?$request['is_change']:1;
        $response = load_model('api/FxTaobaoRefundModel')->td_traned_one($request['sub_order_id'],$is_change);
    }

    function do_traned(array &$request, array &$response, array &$app) {
        
           $response = load_model('api/FxTaobaoRefundModel')->tran($request['ids']);
    }


   }
    