<?php
require_lib ( 'util/web_util', true );
class Service {
	function do_order_list(array & $request, array & $response, array & $app) {
            $ret = load_model('common/ServiceModel')->get_cat();
            $response['category'] = $ret['data'];
	}
	function do_value_list(array & $request, array & $response, array & $app) {
	       $ret = load_model('common/ServiceModel')->get_cat();
            $response['category'] = $ret['data'];
	}
        function translation(array & $request, array & $response, array & $app){
        }
}
