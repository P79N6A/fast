<?php
require_lib ( 'util/web_util', true );
class Spec2 {
	function do_list(array & $request, array & $response, array & $app) {
		//spec2别名
		$arr = array('goods_spec2');
		$arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
		$response['goods_spec2_rename'] =isset($arr_spec2['goods_spec2'])?$arr_spec2['goods_spec2']:'' ;
		
	}
	function detail(array & $request, array & $response, array & $app) {
		$ret = array();
		if (isset($request['_id']) && $request['_id'] != '') {
			$ret = load_model('prm/Spec2Model')->get_by_id($request['_id']);
		}
		$response['data'] = isset($ret['data'])?$ret['data']:'';
		$response['app_scene'] = $_GET['app_scene'];
	}

	function do_edit(array & $request, array & $response, array & $app) {
		
		$spec1 = get_array_vars($request, array('spec2_name','remark'));
		$ret = load_model('prm/Spec2Model')->update($spec1, $request['spec2_id']);
		exit_json_response($ret);
	}

	function do_add(array & $request, array & $response, array & $app) {
		$spec1 = get_array_vars($request, array('spec2_code', 'spec2_name','remark'));
		$ret = load_model('prm/Spec2Model')->insert($spec1);
		exit_json_response($ret);
	}

	
	function do_delete(array & $request, array & $response, array & $app) {
		$ret = load_model('prm/Spec2Model')->delete($request['spec2_id']);
		exit_json_response($ret);
	}
	
	//校验名称是否重复
	function add_check_name(array & $request, array & $response, array & $app) {
		$ret = load_model('prm/Spec2Model')->add_check_name($request);
		exit_json_response($ret);
	}
	
}

