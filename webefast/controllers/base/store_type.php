<?php

/*
 * 仓库相关业务控制器
 */
require_lib('util/web_util', true);

class store_type {

    function do_list(array &$request, array &$response, array &$app) {
        
    }

    function detail(array &$request, array &$response, array &$app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('base/StoreTypeModel')->get_by_id($request['_id']);
        }
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
    }

    function do_add(array &$request, array &$response, array &$app) {
        $store_type = get_array_vars($request, array('type_code', 'type_name', 'remark'));
        $ret = load_model('base/StoreTypeModel')->insert($store_type);
        $response = $ret;
    }
    
    function do_edit(array &$request, array &$response, array &$app) {
        $store_type = get_array_vars($request, array('type_name', 'remark'));
        $ret = load_model('base/StoreTypeModel')->update($store_type, $request['id']);
        $response = $ret;
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/StoreTypeModel')->delete($request['type_code']);
        exit_json_response($ret);
    }

}
