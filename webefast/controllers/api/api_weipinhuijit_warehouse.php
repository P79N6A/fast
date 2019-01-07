<?php

/**
 * 唯品会JIT仓库管理控制器业务
 */
require_lib('util/web_util', true);

class api_weipinhuijit_warehouse {

    function do_list(array &$request, array &$response, array &$app) {
        
    }

    function update_active(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('id', 'active'));
        $response = load_model('api/WeipinhuijitWarehouseModel')->update_active($params);
    }
    function edit_custom(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitWarehouseModel')->edit_custom($request);
        exit_json_response($ret);
    }

}
