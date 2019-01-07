<?php
/*
 *财务退款 
 * */
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms/SellReturnModel');
require_model('oms/SellReturnOptModel');
class sell_return_finance {
	
     //退款列表
    function do_list(array &$request, array &$response, array &$app){
        
    }
    function view(array &$request, array &$response, array &$app){
    	//主单据信息
    	$ret = load_model('oms/SellReturnFinanceModel')->get_by_id($request['sell_return_id']);
    	$sale_channel = load_model('base/SaleChannelModel')->get_row(array('sale_channel_code'=>$ret['data']['sale_channel_code']));
    	$ret['data']['sale_channel_name']= $sale_channel['data']['sale_channel_name'];
    	//print_r($ret);
    	$response['refund_type'] = ds_get_select('refund_type');
    	filter_fk_name($ret['data'], array('return_pay_code|refund_type','store_code|store','shop_code|shop'));
    	$ret['data']['return_type_txt'] = load_model('oms/SellReturnFinanceModel')->return_type[$ret['data']['return_type']];
    	$ret['data']['return_order_status'] = load_model('oms/SellReturnModel')->return_order_status_exp($ret['data']);
    	//取出关联订单信息
    	$response['relation_record'] = load_model("oms/SellRecordModel")->get_record_by_code($ret['data']['sell_record_code']);
    	//关联订单发货状态
    	$response['relation_record']['sell_record_shipping_status_txt'] = load_model('oms/SellRecordModel')->shipping_status[$response['relation_record']['shipping_status']];
    	$return_reason = ds_get_select('return_reason');
    	foreach($return_reason as $v){
    		if($v['return_reason_code'] == $ret['data']['return_reason_code'] ){
    			$ret['data']['return_reason_name'] = $v['return_reason_name'];
    			break;
    		}
    	}
    	$response['record'] = $ret['data'];
        $response['record']['return_reason'] = $return_reason;
    	//print_r($response);
    	//print_r(ds_get_select('return_reason'));
        
        //获取退货商品信息
        $type = array('return_goods');
        $return_info = load_model('oms/SellReturnModel')->component($ret['data']['sell_return_code'], $type);
        if (isset($return_info['data']['detail_list']) && !empty($return_info['data']['detail_list'])) {
            $response['detail_list'] = $return_info['data']['detail_list'];
        }
        unset($return_info);
        //获取换货商品信息
        $type = array('change_goods');
        $change_info = load_model('oms/SellReturnModel')->component($ret['data']['sell_return_code'], $type);
        if (isset($change_info['data']['change_detail_list']) && !empty($change_info['data']['change_detail_list'])) {
            $response['change_detail_list'] = $change_info['data']['change_detail_list'];
        }
        //设置参数启动后必须
        $arr = array('order_return_huo');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['order_return_huo'] =isset($ret_arr['order_return_huo'])?$ret_arr['order_return_huo']:'' ;
        unset($change_info);  
        //沟通日志
        $log=load_model('oms/SellReturnActionModel')->get_communication_log($ret['data']['sell_return_code']);
        $response['communication_log']=$log['action_note'];
        $response['user_name']=$log['user_name'];
    }
    function save(array &$request, array &$response, array &$app){
    	$app['fmt'] = 'json';
    	//修改api_order表
    	$data = get_array_vars($request, array('return_pay_code','return_reason_code'));
    	$ret = load_model('oms/SellReturnFinanceModel')->update($data, $request['sell_return_id']);
    	$response = $ret;
    }
    
    /**
     * 获取退货商品明细
     */
    function get_detail_list_by_sell_return_code(array &$request, array &$response, array &$app){
        $data =  load_model('oms/SellReturnModel')->get_detail_list_by_return_code($request['sell_return_code']);
        $response = array('rows' => $data);
    }
}
