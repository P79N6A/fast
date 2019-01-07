<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class paymentaccount {

    function account_list(array & $request, array & $response, array & $app) {

    }

    function add_account(array & $request, array & $response, array & $app) {
        if (isset($request['id']) && !empty($request['id'])) {
            $ret = load_model('base/PaymentaccountModel')->is_exists($request['id']);
            $response['data'] = $ret;
        }
    }
    
    function do_edit(array & $request, array & $response, array & $app) {
        $ret = load_model('base/PaymentaccountModel')->update($request);
        exit_json_response($ret);
    }

    function alipay(array & $request, array & $response, array & $app) {

    }

    function weixinpay(array & $request, array & $response, array & $app) {

    }

    function add(array & $request, array & $response, array & $app) {
        $ret = load_model('base/PaymentaccountModel')->add($request);
        exit_json_response($ret);
    }

    function delete(array & $request, array & $response, array & $app) {
        $ret = load_model('base/PaymentaccountModel')->delete($request['id']);
        exit_json_response($ret);
    }

    function select_account(array & $request, array & $response, array & $app) {
        $app['page'] = 'NULL';
    }

    function select_account_data(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('base/PaymentaccountModel')->get_account_by_page($request);
        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

}
