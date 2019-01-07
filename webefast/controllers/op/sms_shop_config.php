<?php
require_lib ('util/web_util', true);
class sms_shop_config {
    /**
     * 店铺短信配置列表页面
     */
    function do_list(array &$request, array &$response, array &$app) {
        $response['sale_channel'] = load_model('base/SaleChannelModel')->get_data_code_map();
    }
    /**
     * 店铺短信配置编辑页面
     */
    function detail(array &$request, array &$response, array &$app) {
        $smsTplModel = load_model('op/SmsTplModel');
        $response['select'] = $smsTplModel->get_select_list();
        $shop_code = $request['_id'];
        $shopModel = load_model('base/ShopModel');
        $ret = $shopModel->get_by_code($shop_code);
        if (1 == $ret['status']){
            $response['shop_info'] = $ret['data'];
            $arr_channel = load_model('base/SaleChannelModel')->get_data_code_map();
            $response['shop_info']['sale_channel_name'] = isset($arr_channel[$response['shop_info']['sale_channel_code']]['1']) ? $arr_channel[$response['shop_info']['sale_channel_code']]['1'] : '';
        }
        $ret =  load_model('op/SmsShopConfigModel')->get_by_shop_code($shop_code);
        $response['sms_shop_config'] = $ret['status'] == 1 ? $ret['data'] : array();
    }
    /**
     * 添加或编辑店铺短信配置
     */
    function add_detail(array &$request, array &$response, array &$app) {
        trim_array($request);
        $app['fmt'] = 'json';
        $smsShopConfigModel = load_model('op/SmsShopConfigModel');
        $ret = $smsShopConfigModel->is_exists($request['shop_code'], 'shop_code');
        if (1 == $ret['status']) {
            if (isset($request['is_active']) && 1 == $request['is_active']){
                $ret = $smsShopConfigModel->check_active_allow($request);
                if (1 != $ret['status']) {
                    exit_json_response($ret);
                }
            }
            $ret = $smsShopConfigModel->update($request);
        } else{
            $ret = $smsShopConfigModel->insert($request);
        }
        exit_json_response($ret);
    }
    /**
     * 启用或停用店铺短信配置
     */
    function update_active(array &$request, array &$response, array &$app) {
        if (!isset($request['type']) || !isset($request['shop_code'])) {
            exit_json_response(-1,'','参数缺失');
        }
        $active = $request['type'] == 'enable' ? 1 : 0;
        $smsShopConfigModel = load_model('op/SmsShopConfigModel');
        $ret = $smsShopConfigModel->update_active($active, $request['shop_code']);
        exit_json_response($ret);
    }
}
