<?php

class sales_sku {

    function do_list(array &$request, array &$response, array &$app) {
        $response['sales_no'] = isset($request['sales_no']) ? $request['sales_no'] : '';
        $response['shop'] = load_model('base/ShopModel')->get_wepinhuijit_shop();
        $response['inv_sync'] = load_model('sys/PrivilegeModel')->check_priv('api/wph/sales_sku/inv_sync');
    }

    function inv_sync(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ids = isset($request['ids']) ? $request['ids'] : array($request['id']);
        $response = load_model('api/wph/WphSalesSkuModel')->full_inv_sync($ids);
    }

    function adjust_inv(array &$request, array &$response, array &$app) {
        
    }

    function adjust_data(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $ret = load_model('api/wph/WphSalesSkuModel')->import_adjust_data($request['shop_code'], $file);
        $response = $ret;
        $response['url'] = $_FILES['fileData']['name'];
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

}
