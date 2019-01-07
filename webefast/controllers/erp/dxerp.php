<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class dxerp {

    /**
     *单据同步列表
     * @param array $request
     * @param array $response
     * @param array $ap
     */
    function trade_list(array &$request, array &$response, array &$app) {

    }

    /**
     * 单据上传
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_upload(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('record_code', 'order_type'));
        $ret = load_model('erp/DxerpModel')->record_upload($params['record_code'], $params['order_type']);
        exit_json_response($ret);
    }

    /**
     * 批量上传
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_upload_multi(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('record_code_order_type'));
        $ret = load_model('erp/DxerpModel')->record_upload_multi($params['record_code_order_type']);
        exit_json_response($ret);
    }

    /**
     *定时器上传
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_upload_cli(array &$request, array &$response, array &$app) {
        $ret = load_model('erp/DxerpModel')->do_upload_cli();
        $response['status'] = 1;
    }


}