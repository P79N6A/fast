<?php

class api_weipinhuijit_goods {
    function do_list(array &$request, array &$response, array &$app) {
        $response['shop'] = load_model('base/ShopModel')->get_wepinhuijit_shop();
    }

    function adjust_inv(array &$request, array &$response, array &$app) {

    }

    function get_wepinhuijit_shop(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = load_model('base/ShopModel')->get_wepinhuijit_shop();
        $response = array();
        foreach ($data as $val) {
            $arr = array();
            $arr['text'] = $val['shop_name'];
            $arr['value'] = $val['shop_code'];
            $response[] = $arr;
        }
    }

    function import_stock_data(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $ret = load_model('api/WeipinhuijitGoodsModel')->import_stock_data($request['shop_code'], $file);
        $response = $ret;
        $response['url'] = $_FILES['fileData']['name'];
    }

}
