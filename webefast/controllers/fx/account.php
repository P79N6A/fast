<?php

require_lib('apiclient/AlipaymClient', true);
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('fx/AccountModel', true);

class account {

    function do_list(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/SysParamsModel')->get_val_by_code('fx_finance_account_manage');
        $login_type = CTX()->get_session('login_type');
        $response['login_type'] = $login_type;
        $response['fx_finance_account_manage'] = $ret['fx_finance_account_manage'];
    }

    function detail(array &$request, array &$response, array &$app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('fx/AccountModel')->get_by_id($request['_id']);
        }
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
        $response['data']['account_code'] = load_model('fx/AccountModel')->create_fast_bill_sn();
        $response['pay_type_code'] = load_model('base/PaymentModel')->get_pay_type();
        $response['app_scene'] = $_GET['app_scene'];
    }
    
    function alipay_key(array &$request, array &$response, array &$app) {
        $response = load_model('fx/AccountModel')->get_by_ali_key();
    }
    
    function alipay_do_edit(array &$request, array &$response, array &$app) {
        $response = load_model('fx/AccountModel')->alipay_do_edit(trim($request['key']), trim($request['pid']));
        exit_json_response($response);
    }

    function do_edit(array & $request, array & $response, array & $app) {
        $brand = get_array_vars($request, array('custom_code', 'account_money', 'pay_type', 'remark'));
        $ret = load_model('fx/AccountModel')->update($brand, $request['brand_id']);
        exit_json_response($ret);
    }

    function do_add(array & $request, array & $response, array & $app) {
        $data = get_array_vars($request, array('account_code', 'custom_code', 'account_money', 'pay_type', 'remark'));
        $ret = load_model('fx/AccountModel')->insert($data);
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/AccountModel')->delete($request['account_id']);
        exit_json_response($ret);
    }

    function do_confirm(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/AccountModel')->do_confirm($request['account_id']);
        exit_json_response($ret);
    }

    /**
     * 返回续费结果
     */
    function pay_return(array & $request, array & $response, array & $app) {
        $model = new AccountModel();
        $client_id = $model->get_out_trade_no_cache($request['out_trade_no']);
        $init_info['client_id'] = $client_id;
        CTX()->saas->init_saas_client($init_info);
        load_model('api/ApiKehuModel')->change_db_conn($client_id);

        $alipay = load_model('fx/AccountModel')->get_by_ali_key();
        $p = new AlipaymClient($alipay['pid'], $alipay['key']);
        $status = $p->check_notify_data($request);
        if ($status == 1) {
            $ret = load_model('fx/AccountModel')->handle_info($request);
            echo 'success';
        } else {
            echo 'fail';
        }
        die;
    }

    function skip_new_url(array & $request, array & $response, array & $app) {
        $present_url = load_model('fx/AccountModel')->get_url();
        echo "<script>";
        echo "window.location.href='{$present_url}?app_act=fx/account/recharge_success_jump'";
        echo "</script>";
        die;
    }

    function recharge_success_jump(array & $request, array & $response, array & $app) {

    }

    //页面支付宝支付
    function do_list_ali_pay(array & $request, array & $response, array & $app) {
        $data = get_array_vars($request, array('account_code', 'account_money'));
        $ret = load_model('fx/AccountModel')->do_list_ali_pay($data);
        exit_json_response($ret);
    }

    //验证支付是否成功
    function check_pay_status(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/AccountModel')->check_pay_status($request['serial_number']);
        exit_json_response($ret);
    }

    //明细
    function do_account_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('base/CustomModel')->get_by_code($request['custom_code']);
        $response['data'] = $ret['data'];
    }
    //充值
    function balance_detail_recharge(array & $request, array & $response, array & $app) {
        $response['data'] = $request;
        $response['data']['abstract'] = load_model('fx/BalanceOfPaymentsModel')->get_abstract_select($request['capital_type']);
        $login_type = CTX()->get_session('login_type');
        $response['data']['login_type'] = $login_type;
        $app['act'] = 'balance_detail';
    }
    //扣款
    function balance_detail_deduct_money(array & $request, array & $response, array & $app) {
        $response['data'] = $request;
        $response['data']['abstract'] = load_model('fx/BalanceOfPaymentsModel')->get_abstract_select($request['capital_type']);
        $app['act'] = 'balance_detail';
    }

    /**
     * 余额操作页面
     */
    function balance_detail(array & $request, array & $response, array & $app) {
        $response['data'] = $request;
        $response['data']['abstract'] = load_model('fx/BalanceOfPaymentsModel')->get_abstract_select($request['capital_type']);
    }

    /**
     * 余额操作-充值/扣款
     */
    function opt_balance(array & $request, array & $response, array & $app) {
        $account = load_model('fx/AccountModel');
        $app['fmt'] = 'json';
        if($request['login_type'] == 2) { //分销商登录充值
            $param = get_array_vars($request, array('custom_code', 'money', 'record_time', 'remark','pay_type_code'));
            $param['capital_account'] = 'yck';
            $param['capital_type'] = 1;
            $param['abstract'] = 'cash_recharge';
            //收支明细流水号
            $param['serial_number'] = load_model('fx/BalanceOfPaymentsModel')->create_fast_bill_sn();
            //支付宝还是微信支付（微信没做）
            if($param['pay_type_code'] == 'alipay') { //支付宝支付
                $ret = $account->fx_ali_pay($param);
                $ret['serial_number'] = $param['serial_number'];
            }
            exit_json_response($ret);
        } else{
            $param = get_array_vars($request, array('custom_code', 'capital_account', 'capital_type', 'money', 'record_time', 'abstract', 'remark'));
            $param['detail_type'] = 0;
            $response = $account->opt_balance($param);
        }
    }

    function count_money(array & $request, array & $response, array & $app) {
        $sum_money = load_model('fx/BalanceOfPaymentsModel')->sum_money($request);
        $ret['recharge_money'] = $sum_money['recharge_money'];
        $ret['deduct_money'] = $sum_money['deduct_money'];
        //查询分销商账户余额
        $custom_data = load_model('fx/AccountModel')->get_by_remainder($request['custom_code'], 'yck_account_capital');
        $ret['yck_account_capital'] = empty($custom_data['yck_account_capital']) ? 0 : $custom_data['yck_account_capital'];
        exit_json_response($ret);
    }

    function get_by_account(array & $request, array & $response, array & $app) {
        $request['list_type'] = 'account';
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

    function rush_red(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/BalanceOfPaymentsModel')->cancellation($request['id']);
        exit_json_response($ret);
    }

    function get_balance(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('fx/AccountModel')->get_by_remainder($request['custom_code'], 'yck_account_capital AS yck_balance,arrears_money');
        $response['yck_balance'] += $response['arrears_money'];
    }
    //欠款设置
    function do_arrears_money(array & $request, array & $response, array & $app) {
        $ret = load_model('base/CustomModel')->get_by_code($request['custom_code']);
        $response['data'] = $ret['data'];
    }
    //设置欠款
    function update_arrears(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/AccountModel')->update_arrears($request);
        exit_json_response($ret);
    }

}
