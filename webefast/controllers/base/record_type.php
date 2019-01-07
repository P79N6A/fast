<?php
require_lib('util/web_util', true);
class record_type {
    function do_list(array &$request, array &$response, array &$app) {
    	
    }
    function detail(array &$request, array &$response, array &$app) {
    	
    	$ret = array();
    	if (isset($request['_id']) && $request['_id'] != '') {
        	$ret = load_model('base/RecordTypeModel')->get_by_id($request['_id']);
        	$response['data'] = isset($ret['data'])?$ret['data']:'';
    	}
        
        
    }

    function do_edit(array &$request, array &$response, array &$app) {
		
        $data = get_array_vars($request, array('record_type_name','remark'));
        $ret = load_model('base/RecordTypeModel')->update($data, $request['record_type_id']);
        exit_json_response($ret);
    }

    function do_add(array &$request, array &$response, array &$app) {
    	
        $year = get_array_vars($request, array('record_type_code','record_type_name','remark','record_type_property'));
        $ret = load_model('base/RecordTypeModel')->insert($year);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/RecordTypeModel')->delete($request['record_type_id']);
        exit_json_response($ret);
    }
}
