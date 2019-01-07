<?php
require_lib('util/web_util', true);

class o2o_mgr {

    function upload(array &$request, array &$response, array &$app) {
        $response = load_model('o2o/O2oMgrModel')->upload($request['task_id'],$request['type']);
    }

    function cancel(array &$request, array &$response, array &$app) {
        $response = load_model('o2o/O2oMgrModel')->cancel($request['task_id'],$request['type'],$request['is_cancel_tag']);
    }
    function force_cancel(array &$request, array &$response, array &$app) {
        $response = load_model('o2o/O2oMgrModel')->force_cancel($request['task_id'],$request['type'],$request['is_cancel_tag']);
    }
    function o2o_record_info(array &$request, array &$response, array &$app) {
        $response = load_model('o2o/O2oMgrModel')->get_api_record_info($request['task_id'],$request['type']);
    }

    function order_shipping(array &$request, array &$response, array &$app) {
        $response = load_model('o2o/O2oMgrModel')->order_shipping($request['task_id'],$request['type']);
    }


    //上传wms单据
    function cli_upload_record(array &$request, array &$response, array &$app){
        $obj = load_model('o2o/O2oMgrModel');
        $ret = $obj->upload_record_cli();
        $app['fmt']="json";
        $response['status']=1;
    }

    function cli_o2o_record_info(array &$request, array &$response, array &$app){
        $obj = load_model('o2o/O2oMgrModel');
        $ret = $obj->cli_o2o_record_info();
        $app['fmt']="json";
        $response['status']=1;
    }

    function cli_order_shipping(array &$request, array &$response, array &$app){
        $obj = load_model('o2o/O2oMgrModel');
        $ret = $obj->cli_order_shipping();
        $app['fmt']="json";
        $response['status']=1;
    }

    
}