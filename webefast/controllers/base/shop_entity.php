<?php

require_lib('util/web_util', true);

class shop_entity {

    function do_list(array &$request, array &$response, array &$app) {
        $response['login_type'] = CTX()->get_session('login_type');
    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑商店', 'add' => '添加商店', 'view' => '查看商店');
        $app['title'] = $title_arr[$app['scene']];
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('base/ShopModel')->get_by_id($request['_id']);
        }

        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
        if ($app['scene'] == 'add') {
            $response['data']['shop_code'] = $this->serial_code();
        }
        $response['app_scene'] = $app['scene'];
    }

    //仓库
    function get_store() {
        $arr_store = load_model('base/StoreModel')->get_list();
        $key = 0;
        foreach ($arr_store as $value) {
            $arr_store[$key]['0'] = $value['store_code'];
            $arr_store[$key]['1'] = $value['store_name'];
            unset($arr_store[$key]['store_id'], $arr_store[$key]['store_code'], $arr_store[$key]['store_name']);
            $key++;
        }
        return $arr_store;
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $pattern = "/^([0-1][0-9]|[2][0-3])(:|：)([0-5][0-9])-([0-1][0-9]|[2][0-3])(:|：)([0-5][0-9])$/";
        if (!preg_match($pattern, $request['open_time'])) {
            exit_json_response(-1, '', '营业时间格式为：09:00-18:00');
        } else {
            $shop = get_array_vars($request, array('shop_name', 'shop_user_nick', 'tel', 'address', 'open_time', 'remark', 'province', 'city', 'district', 'street'));
            $ret = load_model('base/ShopModel')->update($shop, $request['shop_id']);
            exit_json_response($ret);
        }
    }

    function do_add(array &$request, array &$response, array &$app) {
        $pattern = "/^([0-1][0-9]|[2][0-3])(:|：)([0-5][0-9])-([0-1][0-9]|[2][0-3])(:|：)([0-5][0-9])$/";
        if (!preg_match($pattern, $request['open_time'])) {
            exit_json_response(-1, '', '营业时间格式为：09:00-18:00');
        } else {
            $shop = get_array_vars($request, array('shop_code', 'shop_name', 'shop_user_nick', 'tel', 'address', 'open_time', 'remark', 'province', 'city', 'district', 'street'));
            $ret = load_model('base/ShopModel')->insert_entity($shop);
            exit_json_response($ret);
        }
    }

    function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('base/ShopModel')->update_entity_active($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }

    /**
     * get店铺代码
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function serial_num(array &$request, array &$response, array &$app) {
        $shop_code = $this->serial_code();
        if ($shop_code) {
            exit_json_response('success', $shop_code, 'xulie');
        } else {
            exit_json_response('error', $shop_code, 'xulie');
        }
    }

    /**
     * 店铺代码生成
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function serial_code() {
        $data = load_model('base/ShopModel')->get_last();
        $id = isset($data[0]['shop_id']) ? intval($data[0]['shop_id']) + 1 : '';
        $len = strlen($id);
        $pinyin = 'POS';
        if ($len == 0) {
            $shop_code = $pinyin . '000';
        } else {
            switch ($len) {
                case 1:
                    $shop_code = $pinyin . '000' . $id;
                    break;
                case 2:
                    $shop_code = $pinyin . '00' . $id;
                    break;
                case 3:
                    $shop_code = $pinyin . '0' . $id;
                    break;
                default:
                    $shop_code = $pinyin . $id;
            }
        }
        return $shop_code;
    }

    /*
     * 获取地址
     */

    function get_area(array &$request, array &$response, array &$app) {
        $parent_id = isset($request['parent_id']) ? $request['parent_id'] : 1;
        $ret = load_model('base/TaobaoAreaModel')->get_area($parent_id);
        exit_json_response($ret);
    }

}
