<?php

class pending_payment {

    function do_list(array &$request, array &$response, array &$app) {
        $login_type = CTX()->get_session('login_type');
        $response['login_type'] = $login_type;
    }

    function view(array &$request, array &$response, array &$app) {
        $response['data'] = load_model('fx/PendingPaymentModel')->get_record($request['record_code']);
    }

    function add(array & $request, array & $response, array & $app) {
        $response['data'] = load_model('fx/PendingPaymentModel')->get_record($request['record_code']);
        $response['custom'] = load_model('fx/AccountModel')->get_by_remainder($response['data']['custom_code'], 'yck_account_capital AS yck_balance,arrears_money');
        $response['custom']['yck_balance'] += $response['custom']['arrears_money'];
        $conf = require_conf('sys/upload');
        $response['upload_path'] = $conf['path']['upload_path'];
    }

    function do_add(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('record_code', 'custom_code', 'online_yck_money', 'offline_pay_time', 'offline_money', 'offline_account_code', 'offline_remark','img_url','thumb_img_url'));
        $response = load_model('fx/PendingPaymentModel')->add_record($data);
    }

}
