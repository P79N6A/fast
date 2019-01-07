<?php

require_lib('util/web_util', true);
class op_activity_goods {
    function do_list(array & $request, array & $response, array & $app){
        
    }
    function do_delete(array & $request, array & $response, array & $app){
        $ret = load_model('op/activity/ActivityGoodsModel')->do_delete($request['id']);
        exit_json_response($ret);
    }
    function rule_goods_import(array &$request, array &$response, array &$app){
  
    }
    function do_edit_detail(array & $request, array & $response, array & $app) {
        $detail = get_array_vars($request, array('shop_code', 'barcode', 'sku', 'inv_num', 'sale_price'));
        $ret = load_model('op/activity/ActivityGoodsModel')->edit_detail_action($request['id'], array($detail));
        exit_json_response($ret);
    }
    function import_goods(array & $request, array & $response, array & $app) {
        set_uplaod($request, $response, $app);
        $ret = check_ext_execl();

        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
    }
     function rule_goods_import_action(array &$request, array &$response, array &$app){
  	$app['fmt'] = 'json';
  	$file = $request['url'];
  	if(empty($file)){
  		$response = array(
  				'status' => 0,
  				'type' => '',
  				'msg' => "请先上传文件"
  		);
  		return $response;
  	}
  	$ret = load_model('op/activity/ActivityGoodsModel')->import_rule_goods($file);
  	$response = $ret;
        
  }
    function inv_check(array &$request, array &$response, array &$app){
        
    }
    
    function inv_update(array &$request, array &$response, array &$app){
//        print_r($request);
//        return;
        if($request['type'] == 'activity_select'){
            $data = load_model('op/activity/ActivityGoodsModel')->get_inv($request['inv_shop']);
        }
        else if($request['type'] == 'execl_select'){
            $data = $request['data'];
        }
        $data_inv = load_model("op/activity/ActivityGoodsModel")->get_data_inv($data,$request['inv_shop']);
        $ret = load_model("api/sys/ApiGoodsModel")->activity_goods_sync_goods_inv_action($data_inv);
        exit_json_response($ret);
    }
    
    
    function check_sku(array &$request, array &$response, array &$app){
        $ret = load_model('op/activity/ActivityGoodsModel')->import_rule_goods_inv($request['url'],$request['shop']);
        $response = $ret;
    }
}
