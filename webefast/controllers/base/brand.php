<?php
require_lib ( 'util/web_util', true );
class Brand {
	function do_list(array & $request, array & $response, array & $app) {
		
	}
	function detail(array & $request, array & $response, array & $app) {
		if(isset($request['_id']) && $request['_id'] != ''){
			$ret = load_model('prm/BrandModel')->get_by_id($request['_id']);
			$response['data'] = $ret['data'];
		}
		$response['app_scene'] = $_GET['app_scene'];
	}

	function do_edit(array & $request, array & $response, array & $app) {
		
		$brand = get_array_vars($request, array('brand_name','brand_logo','remark'));
		$ret = load_model('prm/BrandModel')->update($brand, $request['brand_id']);
		exit_json_response($ret);
	}

	function do_add(array & $request, array & $response, array & $app) {
		$brand = get_array_vars($request, array('brand_code', 'brand_name','brand_logo','remark'));
		$ret = load_model('prm/BrandModel')->insert($brand);
		exit_json_response($ret);
	}

	
	function do_delete(array & $request, array & $response, array & $app) {
		$ret = load_model('prm/BrandModel')->delete($request['brand_id']);
		exit_json_response($ret);
	}
}

