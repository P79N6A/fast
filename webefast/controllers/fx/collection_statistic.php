<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class collection_statistic {

    function do_list(array &$request, array &$response, array &$app) {

    }

    function report_count(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('fx/CollectionStatisticModel')->report_count($request);
    }

    /**详情页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function view(array &$request, array &$response, array &$app) {
        $response['custom_code']= $request['custom_code'];
        $response['store_out_months']= $request['store_out_months'];

    }
}
