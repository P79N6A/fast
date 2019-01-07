<?php

/*
 * 仓库相关业务控制器
 */
require_lib('util/web_util', true);

class store {

    function do_list(array &$request, array &$response, array &$app) {

    }

    function detail(array &$request, array &$response, array &$app) {
        //分销商增值服务
        $response['service_custom'] = load_model('common/ServiceModel')->check_is_auth_by_value('CDKZ');
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('base/StoreModel')->get_by_id($request['_id']);
        }
        //取得国家数据
        $arr_country[0][0] = '';
        $arr_country[0][1] = '请选择国家';
        $arr_area_country = load_model('base/TaobaoAreaModel')->get_area('0');
        $key = 1;
        foreach ($arr_area_country as $value) {
            $arr_country[$key][0] = $value['id'];
            $arr_country[$key][1] = $value['name'];
            $key++;
        }

        $response['area']['country'] = $arr_country;
        //取得省数据
        $arr_province[0][0] = '';
        $arr_province[0][1] = '请选择省';
        if (isset($ret['data']['country'])) {
            $arr_area_province = load_model('base/TaobaoAreaModel')->get_area($ret['data']['country']);
            $key = 1;
            foreach ($arr_area_province as $value) {
                $arr_province[$key][0] = $value['id'];
                $arr_province[$key][1] = $value['name'];
                $key++;
            }
        }
        $response['area']['province'] = $arr_province;
        //取得市数据
        $arr_city[0][0] = '';
        $arr_city[0][1] = '请选择城市';
        if (isset($ret['data']['province'])) {
            $arr_area_city = load_model('base/TaobaoAreaModel')->get_area($ret['data']['province']);
            $key = 1;
            foreach ($arr_area_city as $value) {
                $arr_city[$key][0] = $value['id'];
                $arr_city[$key][1] = $value['name'];
                $key++;
            }
        }
        $response['area']['city'] = $arr_city;

        //取得区县数据
        $arr_district = array();
        $arr_district[0][0] = '';
        $arr_district[0][1] = '请选择区/县';
        if (isset($ret['data']['city'])) {
            $arr_area_district = load_model('base/TaobaoAreaModel')->get_area($ret['data']['city']);
            $key = 1;
            foreach ($arr_area_district as $value) {
                $arr_district[$key][0] = $value['id'];
                $arr_district[$key][1] = $value['name'];
                $key++;
            }
        }
        $response['area']['district'] = $arr_district;
        //取得街道数据
        $arr_street = array();
        $arr_street[0][0] = '';
        $arr_street[0][1] = '请选择街道';
        if (isset($ret['data']['district'])) {
            $arr_area_street = load_model('base/TaobaoAreaModel')->get_area($ret['data']['district']);
            $key = 1;
            foreach ($arr_area_street as $value) {
                $arr_street[$key][0] = $value['id'];
                $arr_street[$key][1] = $value['name'];
                $key++;
            }
        }
        $response['area']['street'] = $arr_street;
        $response['store_type'] = load_model('base/StoreTypeModel')->get_list(0);
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
    }

    function get_area(array &$request, array &$response, array &$app) {
        $parent_id = isset($request['parent_id']) ? $request['parent_id'] : 0;
        $ret = load_model('base/TaobaoAreaModel')->get_area($parent_id);
        //print_r($ret);
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $store = get_array_vars($request, array('allow_negative_inv', 'store_name', 'shop_name', 'shop_contact_person', 'contact_person', 'contact_phone', 'country', 'province', 'city', 'district', 'street', 'address', 'zipcode', 'message', 'message2', 'ship_area_code', 'store_type_code', 'custom_code', 'is_enable_custom'));
        $ret = load_model('base/StoreModel')->update($store, $request['store_id']);
        $response = $ret;
    }

    function do_add(array &$request, array &$response, array &$app) {
        $store = get_array_vars($request, array('allow_negative_inv', 'store_code', 'store_name', 'shop_name', 'shop_contact_person', 'contact_person', 'contact_phone', 'country', 'province', 'city', 'district', 'street', 'address', 'zipcode', 'message', 'message2', 'ship_area_code', 'store_type_code', 'custom_code', 'is_enable_custom'));
        $ret = load_model('base/StoreModel')->insert($store);
        $response = $ret;
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/StoreModel')->delete($request['store_id']);
        exit_json_response($ret);
    }

    function get_nodes(array &$request, array &$response, array &$app) {
        //array('id' => '1', 'text' => '门店仓库')
        $response = array(array('id' => '0', 'text' => '普通仓库'));
    }

    function update_active(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $active = array('enable' => 1, 'disable' => 0);
        $response = load_model('base/StoreModel')->update_active($request['id'], $active[$request['type']]);
    }
    
    function base_selection(array &$request, array &$response, array &$app) {
        switch ($request['select_type']) {
            case 'erp' :
                $request['node_url'] = 'base/store/get_nodes&app_fmt=json';
                $request['dataStore_url'] = 'base/store/erp_store_select_action&app_fmt=json';
        }
    }
    
    function erp_store_select_action(array & $request, array & $response, array & $app) {
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('base/StoreModel')->erp_store_select_action($request);
        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }
   
    
}
