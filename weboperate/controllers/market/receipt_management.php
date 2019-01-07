<?php

class receipt_management {

    //产品发票列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }

    function detail(array & $request, array & $response, array & $app) {
        $response = load_model('market/OspReceiptModel')->get_info_by_receipt_id($request['_id']);
    }

    /**
     * @todo 审核发票
     */
    function check_receipt(array & $request, array & $response, array & $app) {
        $ret = load_model('market/OspReceiptModel')->check_receipt($request['receipt_id']);
        exit_json_response($ret);
    }

    /**
     * @todo 开票操作
     */
    function draw_receipt(array & $request, array & $response, array & $app) {
        $ret = load_model('market/OspReceiptModel')->draw_receipt($request['receipt_id']);
        exit_json_response($ret);
    }
    /**
     * @todo 获取区域
     */
    function get_area(array &$request, array &$response, array &$app) {
        $parent_id = isset($request['parent_id']) ? $request['parent_id'] : 1;
        $ret = load_model('market/OspReceiptModel')->get_area($parent_id);
        exit_json_response($ret);
    }

}
