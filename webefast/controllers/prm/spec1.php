<?php

require_lib('util/web_util', true);

class Spec1 {

    function do_list(array & $request, array & $response, array & $app) {
    	//spec1别名
    	$arr = array('goods_spec1');
    	$arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
    	$response['goods_spec1_rename'] =isset($arr_spec1['goods_spec1'])?$arr_spec1['goods_spec1']:'' ;
  		 
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('prm/Spec1Model')->get_by_id($request['_id']);
        }
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
        $response['app_scene'] = $_GET['app_scene'];
    }

    function do_edit(array & $request, array & $response, array & $app) {

        $spec1 = get_array_vars($request, array('spec1_name', 'remark'));
        $ret = load_model('prm/Spec1Model')->update($spec1, $request['spec1_id']);
        exit_json_response($ret);
    }

    function do_add(array & $request, array & $response, array & $app) {
        $spec1 = get_array_vars($request, array('spec1_code', 'spec1_name', 'remark'));
        $ret = load_model('prm/Spec1Model')->insert($spec1);
        exit_json_response($ret);
    }
    //校验名称是否重复
    function add_check_name(array & $request, array & $response, array & $app) {
    	$ret = load_model('prm/Spec1Model')->add_check_name($request);
    	exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('prm/Spec1Model')->delete($request['spec1_id']);
        exit_json_response($ret);
    }

    function show_alias(array & $request, array & $response, array & $app) {
            $ret = load_model('prm/Spec1Model')->get_by_id($request['_id']);
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
    }

}
