<?php

class wms_item {

    function do_list(array &$request, array &$response, array &$app) {
        //取得wms配置
        $response['wms_config'] = load_model('sys/WmsConfigModel')->get_data_list();
    }

    function get_item(array &$request, array &$response, array &$app) {

    }

    function get_item_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('wms/WmsArchiveModel')->sync_api_barcode($request['wms_config_id'], $request['barcode']);
    }

    function get_wms_config(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $wms_config = load_model('sys/WmsConfigModel')->get_wms_config_select(['jdwms']);
        $response = bui_bulid_select($wms_config);
    }

    /**
     * 上传商品到wms
     */
    function upload_item_to_wms(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('wms/WmsArchiveModel')->sync_batch('add', $request['params']);
    }

    /**
     * 更新商品到wms
     */
    function update_item_to_wms(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('wms/WmsArchiveModel')->sync_batch('update', $request['params']);
    }
    /**
     * 获取上传失败的对应
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_count_by_status(array &$request, array &$response, array &$app) {
        $response['data'] = load_model("wms/WmsItemModel")->get_count_by_status($request);//获取上传失败的数量
    }
}
