<?php
require_lib('util/oms_util', true);
class order_combine_strategy {
	function do_list(array & $request, array & $response, array & $app) {
	}
	
    function update_active(array &$request, array &$response, array &$app) {
    	$ret = load_model('oms/OrderCombineStrategyModel')->update_status( $request['id'],$request['value'],$request['type']);
    	exit_json_response($ret);
    }
	
}
