<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class bs3000j {
    //单据列表
    function trade_list(array &$request, array &$response, array &$app) {
    }
    //生成批发销货单校验
    function upload_multi(array &$request, array &$response, array &$app) {
    	$ret = load_model('erp/Bs3000jModel')->upload_trade($_REQUEST['record_codes']);
    	$response = $ret;
    }
    function wbm_list(array &$request, array &$response, array &$app) {
        
    }
    //上传批发单据
    function wbm_upload(array &$request, array &$response, array &$app){
        $ret = load_model('erp/Bs3000jModel')->wbm_upload($request['record_code']);
        exit_json_response($ret);
    }
    
}