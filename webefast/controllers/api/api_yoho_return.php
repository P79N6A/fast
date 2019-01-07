<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

/**
 * 有货退供单
 * Class api_yoho_delivery
 */
class api_yoho_return {

    /**
     * 退供单列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array &$request, array &$response, array &$app) {
        //获取有货的店铺
        $response['shop'] = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('yoho');
    }

    /**
     * 详情页
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function view(array &$request, array &$response, array &$app) {
        $ret = load_model('api/YohoReturnModel')->get_row(array('id' => $request['id']));
        //获取店铺名称
        $shop_row = load_model('base/ShopModel')->get_by_code($ret['data']['shop_code']);
        $ret['data']['shop_name'] = $shop_row['data']['shop_name'];
        $response = $ret;
    }

    /**
     * 生成退货单的页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function create_view(array &$request, array &$response, array &$app) {
        //退单类型
        $response['return_type'] = load_model('wbm/ReturnNoticeRecordModel')->get_return_type();
    }

    /**
     * 生成批发退货单
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_create(array &$request, array &$response, array &$app) {
        $ret = load_model('api/YohoReturnModel')->create($request);
        $response = $ret;
    }

    function get_notice_record_by_purchase(array & $request, array & $response, array & $app) {
        $ret = load_model('api/YohoReturnModel')->get_notice_record_by_purchase($request['purchase_no']);
        $response = array('rows' => $ret);
    }

    function get_yoho_view(array &$request, array &$response, array &$app) {
        $response['shop'] = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('yoho');
    }


    function get_yoho_by_api(array &$request, array &$response, array &$app) {
        $ret = load_model('api/YohoReturnModel')->get_yoho_by_api($request);
        exit_json_response($ret);
    }

    //批量生成批发退货单校验
    function check_return_more(array &$request, array &$response, array &$app) {
        $ret = load_model('api/YohoReturnModel')->check_return_more($request['return_ids']);
        $response = $ret;
    }
}
