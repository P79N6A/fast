<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

/**
 * 有货出库单
 * Class api_yoho_delivery
 */
class api_yoho_delivery {

    /**
     * 出库单列表
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
        $ret = load_model('api/YohoDeliveryModel')->get_by_field('id', $request['id']);
        //获取店铺名称
        $shop_row = load_model('base/ShopModel')->get_by_code($ret['data']['shop_code']);
        $ret['data']['shop_name'] = $shop_row['data']['shop_name'];
        $ret['data']['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $ret['data']['express_code']));
        $ret['data']['delivery_status'] = ($ret['data']['is_delivery'] == 0) ? '未回写' : '已回写';
        $response = $ret;
    }

    /**
     * 回写，批量回写
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function confirm_delivery(array &$request, array &$response, array &$app) {
        $ret = load_model('api/YohoDeliveryModel')->confirm($request['delivery_no']);
        exit_json_response($ret);
    }



}
