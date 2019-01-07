<?php
require_lib('util/web_util', true);
class order_problem_reason {
    function do_list(array &$request, array &$response, array &$app) {

    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑订单问题原因', 'add' => '添加订单问题原因', 'view' => '查看订单问题原因');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('base/OrderProblemReasonModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('problem_reason_code', 'problem_reason_name', 'is_active', 'remark'));
        $ret = load_model('base/OrderProblemReasonModel')->update($data, $request['problem_reason_id']);
        exit_json_response($ret);
    }

    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('problem_reason_code', 'problem_reason_name', 'is_active', 'remark'));
        $ret = load_model('base/OrderProblemReasonModel')->insert($data, $request['problem_reason_id']);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/OrderProblemReasonModel')->delete($request['problem_reason_id']);
        exit_json_response($ret);
    }

}