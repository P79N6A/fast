<?php

require_lib('util/web_util', true);

class supplier {

    function do_list(array & $request, array & $response, array & $app) {

    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('base/SupplierModel')->get_by_id($request['_id']);
        }
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
        $response['supplier_code'] = $response['data']['supplier_code'];
        $response['data'] = json_encode($response['data']);
        $response['area_info'] = get_array_vars($ret['data'], array('country','province', 'city', 'district', 'street'));
        $response['area_info'] = json_encode($response['area_info']);
        $response['title'] = $app['scene'] == 'add' ? '添加供应商' : '编辑供应商';
    }

    function do_edit(array & $request, array & $response, array & $app) {
        if ($request['type'] == 'base') {
            $spec1 = get_array_vars($request, array('supplier_name', 'rebate',));
        } else {
            $spec1 = get_array_vars($request, array('contact_person', 'province', 'country', 'city', 'district', 'street', 'address', 'zipcode', 'tel', 'mobile', 'fax', 'email', 'website', 'remark'));
        }
        $ret = load_model('base/SupplierModel')->update($spec1, $request['supplier_id'],$request['type']);
        exit_json_response($ret);
    }

    function do_add(array & $request, array & $response, array & $app) {
        $spec1 = get_array_vars($request, array('supplier_code', 'supplier_name', 'rebate'));
        $ret = load_model('base/SupplierModel')->insert($spec1);
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('base/SupplierModel')->delete($request['supplier_id']);
        exit_json_response($ret);
    }
    function get_nodes(array &$request, array &$response, array &$app) {
        $response = array(array('id' => '0', 'text' => '供应商'));
    }
}
