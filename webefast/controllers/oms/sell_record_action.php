<?php
/**
 * 订单日志相关业务
 * @author dfr
 *
 */
require_lib ( 'util/web_util', true );
class sell_record_action {
	//分页列表
	function do_list(array & $request, array & $response, array & $app) {
		
	}
	//列表
	function get_list(array & $request, array & $response, array & $app) {
		$arr_logs = load_model('oms/SellRecordActionModel')->get_by_field('pid','1');
		$response['action_list'] = $arr_logs['data'];
		print_r($response['action_list']);
		exit;
		
	}
	
	/**
	 * 增加日志
	 */
	function do_add(array & $request, array & $response, array & $app) {
		$request = array('pid'=>'3','user_code'=>'b0001','user_name'=>'李四1');
		$data = get_array_vars($request, array('pid', 'user_code','user_name','order_status','shipping_status','pay_status','action_name','action_note'));
		$ret = load_model('oms/SellRecordActionModel')->insert($data);
		exit_json_response($ret);
	}

	/**
	 * 删除日志
	 */
	function do_delete(array & $request, array & $response, array & $app) {
		$ret = load_model('prm/BrandModel')->delete($request['brand_id']);
		exit_json_response($ret);
	}
	
}


