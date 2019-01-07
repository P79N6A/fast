<?php
require_lib('util/web_util', true);
class order_label {
    function do_list(array &$request, array &$response, array &$app) {
        
    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑订单标签', 'add' => '添加订单标签', 'view' => '查看订单标签');
        $app['title'] = $title_arr[$app['scene']];
        if (isset($request['_id']) && $request['_id'] != '') {
        $ret = load_model('base/OrderLabelModel')->get_by_id($request['_id']);
        }
        $response['data'] = isset($ret['data'])?$ret['data']:'';
    }
    
    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('order_label_code', 'order_label_name', 'remark'));
        $ret = load_model('base/OrderLabelModel')->insert($data, $request['order_label_id']);
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('order_label_name' ,'remark'));
        $ret = load_model('base/OrderLabelModel')->update($data, $request['order_label_id']);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/OrderLabelModel')->delete($request['order_label_id']);
        exit_json_response($ret);
    }
}
