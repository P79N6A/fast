<?php
require_lib ( 'util/web_util', true );
require_lib('util/oms_util', true);
class Operate_fx {
	function do_list(array & $request, array & $response, array & $app) {
	
		$response = load_model('crm/OperateModel')->get_report_data();
		//$response['order_sale_money']  = json_encode(array(100,200,300,400,500,600,700));
	}

}
