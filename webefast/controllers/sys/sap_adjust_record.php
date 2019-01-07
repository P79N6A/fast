<?php

require_lib('util/web_util', true);

class sap_adjust_record {

    function do_list(array &$request, array &$response, array &$app) {
        
    }
    function download_data(array &$request, array &$response, array &$app) {
        $params['ZMARK'] = 0;
        $ret = load_model('api/sap/SapApiClientModel')->zr_mes_kctb($params);
        exit_json_response($ret);
    }
    function handle_data(array &$request, array &$response, array &$app) {
        $data = load_model('sys/SapAdjustRecordModel')->get_by_id($request['sap_adjust_record_id']);
        $ret = load_model('sys/SapAdjustRecordModel')->insert(array($data),$data['download_date'],$request['sap_adjust_record_id']);
        exit_json_response($ret);
    }
}
