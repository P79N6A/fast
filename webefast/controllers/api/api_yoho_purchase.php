<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

/**
 * 有货采购单
 * Class api_yoho_puchase
 */
class api_yoho_purchase {

    /**
     * 列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array &$request, array &$response, array &$app) {
        //获取有货平台的店铺
        $response['shop'] = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('yoho');
    }

    /**
     * 详情页
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function view(array &$request, array &$response, array &$app) {
        $ret = load_model('api/YohoPurchaseModel')->get_row(array('purchase_no' => $request['purchase_no']));
        //获取店铺名称
        $shop_row = load_model('base/ShopModel')->get_by_code($ret['data']['shop_code']);
        $ret['data']['shop_name'] = $shop_row['data']['shop_name'];
        $response = $ret;
    }

    /**
     * 生成批发销货单校验
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_check(array &$request, array &$response, array &$app) {
        $ret = load_model('api/YohoPurchaseModel')->check_purchase($request['purchase_id']);
        $response = $ret;
    }


    /**
     * 生成销货单页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function create_view(array &$request, array &$response, array &$app) {
        $shop_data = load_model('base/ShopModel')->get_by_code($request['shop_code']);
        $response['shop']['store_code'] = empty($shop_data['data']['send_store_code']) ? '' : $shop_data['data']['send_store_code'];
        $response['shop']['express_code'] = empty($shop_data['data']['express_code']) ? '' : $shop_data['data']['express_code'];

    }

    /**
     * 生成批发销货单
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_create(array &$request, array &$response, array &$app) {
        $ret = load_model('api/YohoPurchaseModel')->create($request);
        $response = $ret;
    }

    /**
     * 根据采购单获取批发通知单
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_notice_record_by_purchase(array & $request, array & $response, array & $app) {
        $ret = load_model('api/YohoStoreOutRecordModel')->get_notice_record_by_purchase($request['purchase_no']);
        $response = array('rows' => $ret);
    }


    function get_yoho_view(array &$request, array &$response, array &$app) {
        $response['shop'] = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('yoho');
    }

    /**
     * 从接口获取数据
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_yoho_by_api(array &$request, array &$response, array &$app) {
        $ret = load_model('api/YohoPurchaseModel')->get_yoho_by_api($request);
        exit_json_response($ret);
    }

}
