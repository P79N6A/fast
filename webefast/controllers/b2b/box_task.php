<?php
require_lib ( 'util/web_util', true );
require_lib ( 'business_util', true );
class box_task {
	function do_list(array & $request, array & $response, array & $app) {
		$response['task_code'] = $request['task_code'];
	}
	/**
	 * 验收
	 */
	function do_sure(array &$request, array &$response, array &$app){ 
		$ret = load_model('b2b/BoxTaskModel')->update_sure(1,'is_check_and_accept', $request['id']);
		exit_json_response($ret);
	}
	function do_change(array &$request, array &$response, array &$app){
		$ret = load_model('b2b/BoxTaskModel')->update_sure(1,'is_change', $request['id']);
		exit_json_response($ret);
	}
        
       //装箱确认 
       function ys_box(array &$request, array &$response, array &$app){
               $response = load_model('b2b/BoxTaskModel')->ys_box($request);
	      // exit_json_response($ret);

    }
        
        
        
}