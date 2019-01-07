<?php

require_lib('util/web_util', true);
class op_api_activity_check {
    function task_do_list(array & $request, array & $response, array & $app){
        
    }
    function shop_select(array & $request, array & $response, array & $app){
        $ret = load_model('op/activity/ActivityGoodsModel')->shop_select();
        exit_json_response($ret);
    }
    function result(array & $request, array & $response, array & $app){
        
    }
    function start_efficacy(array & $request, array & $response, array & $app){
        $response = load_model('op/activity/ActivityGoodsModel')->start_efficacy($request['shop_code']);
        exit_json_response($response);
    }
    function check_goods(array & $request, array & $response, array & $app){
       
        $ret = load_model('op/activity/OpApiActivityCheckTaskModel')->check_goods( $request['check_sn'],  $request['shop_code']);
        exit_json_response($ret);
    }
    function check_task_list(array &$request, array &$response, array &$app){
        $ret = load_model('op/activity/OpApiActivityCheckTaskModel')->check_task_list();
        exit_json_response($ret);
    }
    function check_goods_task(array &$request, array &$response, array &$app){
        $ret = load_model('op/activity/OpApiActivityCheckTaskModel')->create_check_task();
        $response = $ret;
    }
    
    
}
?>
