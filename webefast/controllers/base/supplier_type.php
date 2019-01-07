<?php

require_lib('util/web_util', true);

class supplier_type {

    function do_list(array & $request, array & $response, array & $app) {
    	
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('base/SupplierTypeModel')->get_by_id($request['_id']);
        }
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
    }

    function do_edit(array & $request, array & $response, array & $app) {

        $spec1 = get_array_vars($request, array('supplier_type_name'));
        $ret = load_model('base/SupplierTypeModel')->update($spec1, $request['supplier_type_id']);
        exit_json_response($ret);
    }

    function do_add(array & $request, array & $response, array & $app) {
        $spec1 = get_array_vars($request, array('supplier_type_code', 'supplier_type_name'));
        $ret = load_model('base/SupplierTypeModel')->insert($spec1);
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('base/SupplierTypeModel')->delete($request['supplier_type_id']);
        exit_json_response($ret);
    }

}

