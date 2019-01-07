<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class o2o_record {

    /**单据列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array &$request, array &$response, array &$app) {

    }

    /**
     * 单据上传
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function record_upload(array &$request, array &$response, array &$app) {
        $ret = load_model('erp/O2oRecordReportModel')->o2o_record_upload($request['id']);
        exit_json_response($ret);
    }


}