<?php

require_lib('util/web_util', true);

class report_jxc {

    function do_list(array & $request, array & $response, array & $app) {
        $purchase_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $response['price_status']['purchase'] = $purchase_status['status'];
        $cost_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('cost_price');
        $response['price_status']['cost'] = $cost_status['status'];
    }

    function sync_data(array & $request, array & $response, array & $app) {
        load_model('prm/JxcReportModel')->sync_data(1);
        $response['status'] = 1;
    }

    function date_update(array & $request, array & $response, array & $app) {
        load_model('prm/JxcReportModel')->date_update();
        //date_update  

        $response['status'] = 1;
    }

    function create_data_base(array & $request, array & $response, array & $app) {
        load_model('prm/JxcReportModel')->create_data_base();
        //date_update  

        $response['status'] = 1;
    }

    /**
     *汇总统计
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function report_count(array & $request, array & $response, array & $app) {
        $ret = load_model('prm/JxcReportModel')->report_count($request);
        exit_json_response($ret);
    }

}
