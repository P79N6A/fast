<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class balance_of_payments {

    function do_list(array &$request, array &$response, array &$app) {
        $login_type = CTX()->get_session('login_type');
        $response['login_type'] = $login_type;
    }

    function get_by_page(array &$request, array &$response, array &$app) {
        $custom_arr = explode(",", $request['custom_code']);
        $request['custom_code'] = implode("','", $custom_arr);
        $model = load_model('fx/BalanceOfPaymentsModel');
        $app['fmt'] = 'json';
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $data = $model->get_by_account($request);
        $response['rows'] = $data['data']['data'];
        $count = $model->account_count($request);
        $response['results'] = $count['count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    function cancellation(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/BalanceOfPaymentsModel')->cancellation($request['id']);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('fx/BalanceOfPaymentsModel')->delete_detail($request['id']);
    }

}
