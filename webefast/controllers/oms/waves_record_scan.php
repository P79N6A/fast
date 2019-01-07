<?php
/**
 * 波次单扫描验货
 * 2017/04/25
 * @author zwj
 */
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms/WavesRecordScanModel');

class waves_record_scan {

    function do_list(array &$request, array &$response, array &$app) {
        $mdl = new WavesRecordScanModel();
        $response['sound'] = $mdl->get_sound();
        $arr = array('print_delivery_record_template', 'clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['print_delivery_record_template'] = isset($ret_arr['print_delivery_record_template']) ? $ret_arr['print_delivery_record_template'] : 0;
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
    }

    function check_waves_record(array &$request, array &$response, array &$app) {
        $mdl = new WavesRecordScanModel();
        $response = $mdl->check_waves(trim($request['waves_record']));
        exit_json_response($response);
    }

    function check_goods_barcode(array &$request, array &$response, array &$app) {
        $mdl = new WavesRecordScanModel();
        $response = $mdl->check_barcode(trim($request['waves_record']), trim($request['goods_barcode']));
        if($response['data']){
            exit_json_response($response['data']);
        }else{
            exit_json_response($response);
        }
    }

    function cancel_express_no(array &$request, array &$response, array &$app) {
        $mdl = new WavesRecordScanModel();
        $response = $mdl->cancel_express_no($request['deliver_record_id']);
        exit_json_response($response);
    }

    function get_express_no(array &$request, array &$response, array &$app) {
        $mdl = new WavesRecordScanModel();
        $response = $mdl->get_express_no($request['deliver_record_id']);
        exit_json_response($response);
    }

    function get_wave_record_is_shipped(array &$request, array &$response, array &$app) {
        $mdl = new WavesRecordScanModel();
        $response = $mdl->get_is_shipped($request['waves_record_id']);
        exit_json_response($response);
    }
}
