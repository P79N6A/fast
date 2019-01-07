<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class Client {

    function do_list(array & $request, array & $response, array & $app) {

    }

    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑顾客信息', 'add' => '添加顾客', 'view' => '查看顾客信息');
        $app['title'] = $title_arr[$app['scene']];
        $response['data'] = array();
        if ($app['scene'] == 'edit' && isset($request['_id']) && $request['_id'] != '') {
            $response['data'] = load_model('crm/ClientModel')->get_detail_by_id($request['_id']);
        }

        if ($app['scene'] == 'add' || $request['scene'] == 'add') {
            $response['data']['shop_code'] = load_model('crm/ClientModel')->serial_code();
            $app['scene'] = 'add';
            if (isset($request['tel'])) {
                $response['data']['tel'] = $request['tel'];
            }
        }
    }

    function do_edit(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('client_code', 'client_name', 'client_tel', 'client_sex', 'birthday', 'email', 'province', 'city', 'district', 'street', 'address', 'remark'));
        $response = load_model('crm/ClientModel')->update($data);
    }

    function do_add(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('client_code', 'client_name', 'client_tel', 'client_sex', 'birthday', 'email', 'province', 'city', 'district', 'street', 'address', 'remark'));
        $response = load_model('crm/ClientModel')->add_client($data);
    }

}
