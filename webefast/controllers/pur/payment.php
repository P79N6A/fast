<?php

require_lib('util/web_util', true);
require_lib('business_util', true);

class payment {

    function do_list(array & $request, array & $response, array & $app) {
        
    }
    function do_view_payment(array & $request, array & $response, array & $app) {
        //获取单据信息
        $response['data'] =  load_model('pur/AccountsPayableModel')->get_record_info($request['record_code']);
    }
    function get_by_page_record(array & $request, array & $response, array & $app) {
       $app['fmt'] = 'json';
       $data = load_model('pur/PaymentModel')->get_by_page_record($request['record_code']);
       $response['rows'] = $data;
       $response['hasError'] = false;
       $response['error'] = '';
    }
    function do_cancellation(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/PaymentModel')->do_cancellation($request['serial_number']);
	exit_json_response($ret);
    }
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/PaymentModel')->do_delete($request['serial_number']);
	exit_json_response($ret);
    }
    function statistical(array & $request, array & $response, array & $app) {
        
    }
    function payment_count(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/PaymentModel')->payment_count($request);
	exit_json_response($ret);
    }
}
