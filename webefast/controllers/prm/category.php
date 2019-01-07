<?php
require_lib ( 'util/web_util', true );
class Category {
	function do_list(array & $request, array & $response, array & $app) {

	}

        function get_nodes(array & $request, array & $response, array & $app){
		$response = load_model('prm/CategoryModel')->get_child($request['id']);
		//exit_json_response($ret);
	}
	function detail(array & $request, array & $response, array & $app) {
		$ret = array();
		if (isset($request['_id']) && $request['_id'] != '') {
			$ret = load_model('prm/CategoryModel')->get_by_id($request['_id']);
		}
		if((isset($request['child']) && $request['child'] == '1')){
			$p_id = $ret['data']['category_id'];
			unset($ret);
			$ret = load_model('prm/CategoryModel')->get_by_id($p_id);
			//$p_id = $ret['data']['category_id'];
			$p_id = $ret['data']['category_code'];
			$p_id_name = $ret['data']['category_name'];
			$p_id_code = $ret['data']['category_code'];
			$ret['data'] = array();
			$ret['data']['p_code'] = $p_id;
			$ret['data']['p_code_name'] = $p_id_name;
			$ret['data']['p_code_code'] = $p_id_code;
		}
                if(isset($request['p_code'])){
                    $p_data = load_model('prm/CategoryModel')->get_row(array('category_code'=>$ret['data']['p_code']));
                    $ret['data']['p_code_name'] = $p_data['data']['category_name'];
                    $ret['data']['p_code_code'] = $p_data['data']['category_code'];
                }
		$response['data'] = isset($ret['data'])?$ret['data']:'';
	}
	
	function do_edit(array & $request, array & $response, array & $app) {
		$category = get_array_vars($request, array('category_name','p_code','remark'));
		$ret = load_model('prm/CategoryModel')->update($category, $request['category_id']);
		exit_json_response($ret);
	}
	
	function do_add(array & $request, array & $response, array & $app) {
		$category = get_array_vars($request, array('category_code', 'category_name','remark'));
		if(!empty($request['p_code']))
		$category['p_code'] = $request['p_code'];
		else 
		$category['p_code'] = 0;
		$ret = load_model('prm/CategoryModel')->insert($category);
		exit_json_response($ret);
	}
	
	
	function do_delete(array & $request, array & $response, array & $app) {
		$ret = load_model('prm/CategoryModel')->delete($request['category_id']);
		exit_json_response($ret);
	}
	
}