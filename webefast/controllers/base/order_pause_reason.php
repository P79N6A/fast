<?php
require_lib('util/web_util', true);
class order_pause_reason {
    function do_list(array &$request, array &$response, array &$app) {

    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑订单挂起原因', 'add' => '添加订单挂起原因', 'view' => '查看订单挂起原因');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('base/OrderPauseReasonModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('pause_reason_code', 'pause_reason_name', 'is_active', 'remark'));
        $ret = load_model('base/OrderPauseReasonModel')->update($data, $request['pause_reason_id']);
        exit_json_response($ret);
    }

    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('pause_reason_code', 'pause_reason_name', 'is_active', 'remark'));
        $ret = load_model('base/OrderPauseReasonModel')->insert($data, $request['pause_reason_id']);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/OrderPauseReasonModel')->delete($request['pause_reason_id']);
        exit_json_response($ret);
    }

}