<?php
require_lib ('util/web_util', true);
class sys_oms {
    function do_index(array &$request, array &$response, array &$app) {
    	$arr = array('fanance_money','off_deliver_time','oms_notice');
		$response = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $app['page'] = NULL;
    }
    
    function update_params(array &$request, array &$response, array &$app) {
//    	print_r($request);exit;
    	if(!empty($request['fanance_money'])){
    	$where = "param_code = 'fanance_money' ";
    	$data = array('value'=> $request['fanance_money']);
    	$ret = load_model('sys/SysParamsModel')->update($data,$where);
    	}else{

    	$where = "param_code = 'oms_notice' ";
    	$data = array('value'=> $request['oms_notice']);
    	$ret = load_model('sys/SysParamsModel')->update($data,$where);
    	
    	$where = "param_code = 'off_deliver_time' ";
    	$data = array('value'=> $request['off_deliver_time']);
    	$ret = load_model('sys/SysParamsModel')->update($data,$where);
    		
    	}
		
        exit_json_response($ret);
    }
}

