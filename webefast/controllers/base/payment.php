<?php
require_lib('util/web_util', true);
class payment {
    function do_list(array &$request, array &$response, array &$app) {

    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑支付方式', 'add' => '添加支付方式', 'view' => '查看支付方式');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('base/PaymentModel')->get_row(array('pay_type_id'=>$request['_id']));
        $response['data'] = $ret['data'];
        $response['json_form_data'] = json_encode($ret['data'], true);
    }
    function add(array &$request, array &$response, array &$app) {
        
    }
    //新增支付方式
    function do_add(array &$request, array &$response, array &$app) {
        $ret = load_model('base/PaymentModel')->insert($request);
        exit_json_response($ret);
    }
    function do_edit(array &$request, array &$response, array &$app) {
        $user = get_array_vars($request, array('remark'));
        $ret = load_model('base/PaymentModel')->update($user, $request['pay_type_id']);
        exit_json_response($ret);
    }

}