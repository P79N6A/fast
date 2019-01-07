<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class bs3000j_inv_sync {
    //单据列表
    function trade_list(array &$request, array &$response, array &$app) {
    }
    //生成批发销货单校验
    function upload_multi(array &$request, array &$response, array &$app) {
    	$ret = load_model('erp/Bs3000jModel')->upload_trade($_REQUEST['record_codes']);
    	$response = $ret;
    }
    
    function get_inv_and_update(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $ret = load_model("erp/Bs3000jInvSyncModel")->get_inv_and_update($request);
    	$response = $ret;
    }
}