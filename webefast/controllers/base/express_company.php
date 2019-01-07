<?php
require_lib('util/web_util', true);
class express_company {
    function do_list(array &$request, array &$response, array &$app) {
	
    }

    function get_data_from_api(array &$request, array &$response, array &$app) {
	    $ret = load_model('base/ExpressCompanyModel')->get_data_from_api();
	    if ($ret['status']<>1){
	    	echo "<h2>更新快递公司列表出错".$ret['message']."</h2>";
	    	die;
	    }else{
	    	echo "<h2>更新快递公司列表成功</h2>";
	    	die;
	    }
    }
    
	function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('base/ExpressCompanyModel')->update_active($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }
    
    function table(array &$request, array &$response, array &$app){
        if (isset($request['is_active']) && $request['is_active'] != '') {
	    	 if ($request['is_active'] == 'active')
	    	 $response['is_active'] = 1;
	    	 else 
	    	 $response['is_active'] = 0;
	    }
    }
    
    function edit_param(array &$request, array &$response, array &$app) {
       $ret = load_model('base/ExpressCompanyModel')->get_api_content($request['company_code']);
       
       $response['data'] = $ret['data'];
    }
    
    function do_edit_param(array &$request, array &$response, array &$app){
        $response =  load_model('base/ExpressCompanyModel')->save_api_content($request);
    }
}