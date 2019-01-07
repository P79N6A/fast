<?php
require_lib('util/web_util', true);
class sms_supplier {
    function do_list(array &$request, array &$response, array &$app) {
    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑短信供应商', 'add' => '添加短信供应商', 'view' => '查看短信供应商');
        $app['title'] = $title_arr[$app['scene']];
        if ($app['scene'] == 'edit') {
            $ret = load_model('sys/SmsSupplierModel')->get_by_id($request['_id']);
        }
        $response['data'] = $ret['data'];
    }

    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('supplier_code', 'supplier_name', 'unit_price', 'server_ip', 'server_port', 'is_active', 'remark'));
        $ret = load_model('sys/SmsSupplierModel')->insert($data, $request['supplier_id']);
        exit_json_response($ret);
    }

    function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('sys/SmsSupplierModel')->update_active($arr[$request['type']], $request['supplier_id']);
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('supplier_code', 'supplier_name', 'unit_price', 'server_ip', 'server_port', 'is_active', 'remark'));
        $ret = load_model('sys/SmsSupplierModel')->update($data, $request['supplier_id']);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/SmsSupplierModel')->delete($request['supplier_id']);
        exit_json_response($ret);
    }
}
