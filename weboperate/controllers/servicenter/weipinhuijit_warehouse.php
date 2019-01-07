<?php

/*
 * 地址库
 */

class weipinhuijit_warehouse {

    /**列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array & $request, array & $response, array & $app) {

    }

    function detail(array & $request, array & $response, array & $app) {

    }

    function do_add(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('warehouse_code', 'warehouse_name','desc','warehouse_no'));
        $params['status'] = 0;
        $ret = load_model('servicenter/WeipinhuijitWarehouseModel')->add_action($params);
        exit_json_response($ret);
    }


}