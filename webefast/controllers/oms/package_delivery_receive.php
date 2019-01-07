<?php

/**
 * 包裹快递交接
 * 2017/04/12
 * @author zwj
 */
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms/PackageDeliveryReceivedModel');

class package_delivery_receive {

    function do_list(array &$request, array &$response, array &$app) {
        $mdl = new PackageDeliveryReceivedModel();
        $response['sound'] = $mdl->get_sound();
    }

    //扫描交接
    function scan_receive(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $express_no = str_replace('-1-1-', '', trim($request['express_no']));
        $response = load_model('oms/PackageDeliveryReceivedModel')->express_scan_receive($express_no); 
    }

    //统计
    function count_list(array &$request, array &$response, array &$app) {
        
    }

    //统计列表
    function base_list(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    //统计饼图
    function base_picture(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    //统计
    function get_picture_data(array &$request, array &$response, array &$app) {
        $mdl = new PackageDeliveryReceivedModel();
        $response = $mdl->get_picture_count_data($request);
        exit_json_response($response);
    }

}
