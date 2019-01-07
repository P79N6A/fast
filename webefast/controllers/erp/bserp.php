<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class bserp {
    //单据列表
    function trade_list(array &$request, array &$response, array &$app) {
    }
    
    function wbm_list(array &$request, array &$response, array &$app) {
        
    }
    
    function wbm_upload(array &$request, array &$response, array &$app){
        $ret = load_model('erp/BserpModel')->wbm_upload($request['record_code']);
        exit_json_response($ret);
    }
    
    //批量单据上传
    function upload_multi(array &$request, array &$response, array &$app) {
    	$ret = load_model('erp/BserpModel')->upload_trade($_REQUEST['record_codes']);
    	$response = $ret;
    }
    function inv_sync_trade_list(array &$request, array &$response, array &$app) {
        
    }
    function get_inv_and_update(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $ret = load_model("erp/BserpInvSyncModel")->get_inv_and_update($request);
    	$response = $ret;
    }
    
    function erp_do_list(array &$request, array &$response, array &$app){
        $response['store'] = load_model('erp/BserpModel')->get_sys_api_store();
        $response['shop'] = load_model('erp/BserpModel')->get_sys_api_shop();
        $response['record_type'] = load_model('erp/BserpModel')->get_select_record_type();
    }
    
    function create_daily_report(array &$request, array &$response, array &$app){
        
    }
    
    function create_daily_report_action(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('record_type', 'record_date', 'shop_code', 'store_code', 'is_fenxiao', 'remark'));
        $ret = load_model("erp/BserpModel")->create_daily_report_action($data);
    	$response = $ret;
    }
    
    function daily_report_detail(array &$request, array &$response, array &$app){
        $response['data'] = load_model('erp/BserpModel')->get_daily_report_detail($request['id']);
    }
}