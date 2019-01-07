<?php
require_lib('util/web_util', true);
//require_lib('util/oms_util', true);
class sell_record_cz {
	function do_list(array & $request, array & $response, array & $app) {
		
	}
	//称重设置
	function config(array & $request, array & $response, array & $app) {
		$ret = load_model('oms/SellRecordCzModel')->config_detail();
		$response['data'] = empty($ret)?'':$ret;
		$response['app_scene'] = 'add';
	}
	//称重设置
	function save_config(array & $request, array & $response, array & $app) {
		$response = load_model('oms/SellRecordCzModel')->save_config($request);
	}
	//称重界面
	function view(array & $request, array & $response, array & $app) {
		$response['config'] = load_model('oms/SellRecordCzModel')->config_detail();
		$weight_info = load_model('sys/SysParamsModel')->get_val_by_code(array('weight_different_notice','weight_different','warn_weight'));
        $response['weight_info'] = $weight_info;
	}

    /**
     *预警重量比较
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function warn_weight_check(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request,array('cz_weight'));
        $ret=load_model('oms/SellRecordCzModel')->warn_weight_check($params);
        exit_json_response($ret);
    }

	//获取理论重量
	function get_record_weight(array & $request, array & $response, array & $app) {
		$response = load_model('oms/SellRecordCzModel')->get_record_weight($request['sell_record_code']);
	}
    function get_record_goods_weight(array & $request, array & $response, array & $app) {
        $response = load_model('oms/SellRecordCzModel')->get_record_goods_weight($request['sell_record_code']);
    }
	//计算称重运费
	function get_cz_express_money(array & $request, array & $response, array & $app) {
		$response = load_model('oms/SellRecordCzModel')->get_weigh_express_money($request['sell_record_code'],$request['cz_weight']);
	
	}
	function search_sell_record(array & $request, array & $response, array & $app) {
                $request['express_no'] = str_replace('-1-1-','',trim($request['express_no']));
		$response = load_model('oms/SellRecordCzModel')->search_sell_record($request['express_no']);
	
	}
	//自动确认
	function confirm(array & $request, array & $response, array & $app) {
		$response = load_model('oms/SellRecordCzModel')->confirm($request);
	
	}
	
        function no_weighing_list(array & $request, array & $response, array & $app){
            
        }

}
