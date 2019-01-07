<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class api_weipinhuijit_delivery {

    //出库单列表
    function do_list(array &$request, array &$response, array &$app) {
        $response['batch_confirm_delivery'] = load_model('sys/PrivilegeModel')->check_priv('api/api_weipinhuijit_delivery/batch_confirm_delivery');;
        $response['shop'] = load_model('base/ShopModel')->get_wepinhuijit_shop();
    }

    //创建出库单
    function create_view(array &$request, array &$response, array &$app) {
        $response['data']['ES_frmId'] = $request['ES_frmId'];
    }

    //创建出库单逻辑
    function do_create(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitDeliveryModel')->create_delivery($request['out_ids']);
        $response = $ret;
    }

    //确认出库
    function confirm_delivery(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitDeliveryModel')->confirm($request['delivery_id']);
        $response = $ret;
    }
    //批量确认出库
    function batch_confirm_delivery(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitDeliveryModel')->confirm($request['params']['delivery_id']);
        $response = $ret;
    }

    //详情
    function view(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitDeliveryModel')->get_row(array('id' => $request['id']));
        $ret['data']['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $ret['data']['express_code']));
        $shop_row = load_model('base/ShopModel')->get_by_code($ret['data']['shop_code']);
        $ret['data']['shop_name'] = $shop_row['data']['shop_name'];
        $brand_row = load_model('prm/BrandModel')->get_by_field('brand_code', $ret['data']['brand_code']);
        $ret['data']['brand_name'] = $brand_row['data']['brand_name'];
        $warehouse_arr = load_model('api/WeipinhuijitPickModel')->weipinhui_warehouse();
        $ret['data']['warehouse_name'] = $warehouse_arr[$ret['data']['warehouse']]['name'];
        $ok = get_theme_url('images/ok.png');
        $no = get_theme_url('images/no.gif');
        if ($ret['data']['is_delivery'] == '1') {
            $is_delivery_src = $ok;
        } else {
            $is_delivery_src = $no;
        }
        $ret['data']['is_delivery_src'] = "<img src='{$is_delivery_src}'>";

        $response = $ret;
    }

    function get_info(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitDeliveryModel')->get_row(array('delivery_id' => $request['delivery_id']));
        $time_arr = explode(' ', $ret['data']['arrival_time']);
        $ret['data']['arrival_time'] = $time_arr[0];
        $ret['data']['time_slot'] = $time_arr[1];
        $response = $ret['data'];
    }

    /**修改信息
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function edit_deliver_view(array & $request, array & $response, array & $app) {
        $deliver = load_model('api/WeipinhuijitDeliveryModel')->get_by_id($request['id']);
        $response['data'] = $deliver['data'];
        if (!empty($response['data']['arrival_time'])) {
            $response['data']['arrival_date'] = date('Y-m-d', strtotime($response['data']['arrival_time']));
            $response['data']['time_slot'] = date('H:i:s', strtotime($response['data']['arrival_time']));
        } else {
            $response['data']['time_slot'] = '';
        }
    }

    function edit_deliver_action(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('id', 'arrival_time', 'delivery_method','express_code','express','po_no'));
        $ret = load_model('api/WeipinhuijitDeliveryModel')->edit_deliver_action($params);
        exit_json_response($ret);
    }

}
