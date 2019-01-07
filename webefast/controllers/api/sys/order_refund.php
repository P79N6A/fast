<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class order_refund {

    function do_list(array &$request, array &$response, array &$app) {
      $response['change_fail_num'] = load_model('oms/OrderMenuTipModel')->get_fail_refund_num();
      //页面操作权限获取
        $priv_map = array(
            'opt_set_refund' => 'oms/order_refund/opt_set_refund',
            'opt_set_is_change' => 'oms/order_refund/opt_set_is_change',
            'opt_set_no_change' => 'oms/order_refund/opt_set_no_change',
        );
        $obj_priv = load_model('sys/PrivilegeModel');
        $priv_arr = array();
        foreach ($priv_map as $opt => $val) {
            $priv_arr[$opt] = $obj_priv->check_priv($val);
        }
        $response['power'] = $priv_arr;
        $response['refund_id'] = isset($request['refund_id']) ? $request['refund_id'] : '';
        $response['do_search'] = !empty($response['refund_id']) ? 1 : 0;
    }

    function view(array &$request, array &$response, array &$app) {
        $response['refund'] = load_model('api/sys/OrderRefundModel')->get_refund($request['refund_id']);
        $response['from'] = $request['from'];
    }

    function edit(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('api/sys/OrderRefundModel')->update_detail($request['data']);
    }

    function send(array &$request, array &$response, array &$app) {
        $mdl = new OrderSendModel();
        $response = $mdl->update_by_id($request['id']);
    }

    function set_is_change(array &$request, array &$response, array &$app) {
        $response = load_model('api/sys/OrderRefundModel')->set_change_status($request['refund_id'],1);
    }
    
    //批量设订单处理状态
    function set_change_status (array &$request, array &$response, array &$app) {
        $response = load_model('api/sys/OrderRefundModel')->set_change_status($request['refund_id'],$request['change']);       
    }

    function cli_trans(array &$request, array &$response, array &$app) {
        load_model('oms/TranslateRefundModel')->cli_trans();
        die;
    }

    /**
     * 退单下载
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function down(array &$request, array &$response, array &$app) {
        $sale_channel = load_model('base/SaleChannelModel')->get_select();
        $sale_channel_arr = array();
        foreach ($sale_channel as $key => $value) {
            if ($value[0] == 'taobao' || $value[0] == 'jingdong') {
                $sale_channel_arr[] = $value;
            }
        }
        $response['sale_channel'] = $sale_channel_arr;
        $sale_channel_code = $sale_channel_arr[0][0];
        $response['shop'] = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code($sale_channel_code);
    }

    /**
     * 调用efast_api下载退单
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function down_refund(array &$request, array &$response, array &$app){
        $ret = load_model('api/sys/OrderRefundModel')->down_refund($request);
        exit_json_response($ret);
    }

    /**
     * 下载进度
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function down_refund_check(array &$request, array &$response, array &$app){
        $ret = load_model('api/sys/OrderRefundModel')->down_refund_check($request);
        exit_json_response($ret);
    }

}
