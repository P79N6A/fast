<?php

/*
 * 零售结算明细单
 */

/**
 * Description of retail_settlement_detail
 *
 * @author user
 */
require_lib('comm_util', true);
class retail_settlement_detail {
    //put your code here
    function do_list(array & $request, array & $response, array & $app){
        $this->get_spec_rename($response);
    }
    
    function add(array & $request, array & $response, array & $app){
        
    }
    
    function do_add(array & $request, array & $response, array & $app){
    	$ret = load_model("oms/SellSettlementModel")->new_settlement_adjust($request);
    	exit_json_response($ret);
    }
    
    //获取商品级的明细
    function get_detail_list_by_deal_code(array & $request, array & $response, array & $app){
        if (isset($request['deal_code']) && isset($request['order_attr']) && isset($request['settle_type'])) {
            if ((1 == $request['order_attr'] && 2 == $request['settle_type']) || (2 == $request['order_attr'] && 3 == $request['settle_type'])) {
                $response = array('rows'=>null);
            } else {
                $data = load_model("oms/SellSettlementModel")->get_detail_by_deal_code($request['deal_code'],$request['order_attr'],$request['sell_record_code']);
                $response = array('rows'=>$data);
            }
        } else {
            $response = array('rows'=>null);
        }
    }
    
    private function get_spec_rename(array &$response){
        //spec别名
        $arr = array('goods_spec1','goods_spec2');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
    }
    //导入核销
    function dz_import(array &$request, array &$response, array &$app) {
    
    }
    
    function import_action(array &$request, array &$response, array &$app) {
    	set_time_limit(0);
    	$app['fmt'] = 'json';
    	$file = $request['url'];
    	if(empty($file)){
    		$response = array(
    				'status' => 0,
    				'type' => '',
    				'msg' => "请先上传文件"
    		);
    	}
    	$response = load_model('acc/OmsSellSettlementModel')->import_dz($file);
    
    }
    
    function import_upload(array &$request, array &$response, array &$app) {
       $ret = check_ext_execl();
         set_uplaod($request, $response, $app);
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
    	//$app['fmt'] = 'json';
    	$response = import_upload();
    }
}
