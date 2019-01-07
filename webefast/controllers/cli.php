<?php

require_lib('util/web_util', true);

class cli {

    function auto_trans_api_refund(array & $request, array & $response, array & $app) {
        load_model('oms/SellReturnModel')->refund_finish_cancel_return();
        load_model('oms/TranslateRefundModel')->cli_trans();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    function auto_trans_api_order(array & $request, array & $response, array & $app) {
        load_model('oms/TranslateOrderModel')->cli_trans_exec();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    function cli_trans_api_order(array & $request, array & $response, array & $app) {
        load_model('oms/TranslateOrderModel')->cli_trans($request);
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    function set_cli_trans_max(array & $request, array & $response, array & $app) {
        load_model('oms/TranslateOrderModel')->set_cli_trans_max($request);
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    function auto_trans_api_fenxiao_order(array & $request, array & $response, array & $app) {
        load_model('oms/TranslateOrderModel')->cli_trans_fenxiao();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    function auto_trans_api_fenxiao_refund(array & $request, array & $response, array & $app) {
        load_model('oms/TranslateRefundModel')->cli_trans_each_fx();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    function cli_decrypt_api_order(array & $request, array & $response, array & $app) {
        load_model('sys/security/CustomersSecurityOptModel')->cli_decrypt_api_order();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    function cli_encrypt_history(array & $request, array & $response, array & $app) {
        load_model('sys/security/HistorySecurityOptModel')->exec_security($request);
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    function cli_encrypt_task(array & $request, array & $response, array & $app) {
        load_model('sys/security/SysEncrypRecordModel')->create_task();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

}
