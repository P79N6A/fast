<?php

require_lib('util/web_util', true);

class sap_sell_record {

    function do_list(array &$request, array &$response, array &$app) {
        
    }

    
        function insert_record(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/SapSellRecordModel')->insert_record();
        exit_json_response($ret);
    }
    //单个上传
    function do_upload(array &$request, array &$response, array &$app) {
        $sap_model  = load_model('sys/SapSellRecordModel');
        $data = $sap_model->get_by_id($request['sap_record_id']);
        if(empty($data)) {
            $ret = $sap_model->update_error($request['sap_record_id']);
            exit_json_response($ret);
        }   
        $ret = $sap_model->upload_data($data,$request['sap_record_id']);
        exit_json_response($ret);
    }
    //批量上传
    /*function all_upload(array &$request, array &$response, array &$app) {
        $sap_record_id_str = "'" . implode("','", $request['sap_record_id']) . "'";
        $data = load_model('sys/SapSellRecordModel')->get_by_id($sap_record_id_str);
        $ret = load_model('sys/SapSellRecordModel')->upload_data($data);
        exit_json_response($ret);
    }*/
    //生成积分
    function insert_integral(array &$request, array &$response, array &$app) {
        load_model('sys/SapSellRecordModel')->insert_integral();
        $response['status'] = 1;
    }
    //自动服务上传单据
    function uploade_automatism(array &$request, array &$response, array &$app) {
        load_model('sys/SapSellRecordModel')->uploade_automatism();
        $response['status'] = 1;
    }
    //自动更新单据
    function insert_automatism(array &$request, array &$response, array &$app) {
        load_model('sys/SapSellRecordModel')->insert_record();
        $response['status'] = 1;
    }
}
