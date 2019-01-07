<?php

/**
 * 积分报表统计
 * Class integral_report
 */
class integral_report {

    /**
     * 积分统计列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array & $request, array & $response, array & $app) {
        //获取淘宝和京东的店铺
        $purview_sale_channel = array('taobao', 'jingdong');
        $purview_shop_arr = array();
        foreach ($purview_sale_channel as $sale_channel_code) {
            $shop_info = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code($sale_channel_code);
            $purview_shop_arr = array_merge($purview_shop_arr, $shop_info);
        }
        $response['purview_shop'] = $purview_shop_arr;
    }


    /**
     * 淘宝页签
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function taobao(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    /**
     * 京东页签
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function jingdong(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

}
