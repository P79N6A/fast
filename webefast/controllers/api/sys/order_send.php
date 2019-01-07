<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
//require_model('oms/TaobaoRecordModel', true);
require_model('oms/SellRecordFixModel', true);
//require_model('oms/SellRecordOptModel', true);



require_model("sys/SysTaskModel");
require_model("api/sys/OrderSendModel");

class order_send {

    function index(array &$request, array &$response, array &$app) {
        $response['source'] = load_model('base/SaleChannelModel')->get_data_code_map();
        $m = new SellRecordModel();
        $is_back = $m->get_select_is_back();
        $response['num'] = load_model("sys/OrderSendModel")->get_count_by_status(-1);
        $response['is_back'] = $is_back;
        $app['tpl'] = "api/sys/order_send/index";

        $ret = load_model('sys/ParamsModel')->get_by_field('param_code', 'send_check_refund', 'value');
        $response['is_check_refund'] = $ret['data']['value'];
    }

    function send(array &$request, array &$response, array &$app) {
        $mdl = new OrderSendModel();
        $response = $mdl->update_by_id($request['id']);
    }

    /**
     * 单条回写
     */
    function send_order(array &$request, array &$response, array &$app) {
        $response = load_model('api/sys/OrderSendModel')->send_order($request['id'], $request['force_send'], 0);
    }

    /**
     * 批量回写
     */
    function batch_send_order(array &$request, array &$response, array &$app) {
        $response = load_model('api/sys/OrderSendModel')->send_order($request['api_order_send_id'], $request['force_send'], 1);
    }

    /**
     * 本地回写
     */
    function send_local(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $type = $request['send_type'] == 'batch' ? 'batch' : 'one';
        $response = load_model('api/sys/OrderSendModel')->send_local($request['api_order_send_id'], $type);
    }

}
