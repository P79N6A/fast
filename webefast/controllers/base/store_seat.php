<?php
require_lib('util/web_util', true);
class store_seat {
    function do_list(array &$request, array &$response, array &$app) {

    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑仓库库位', 'add' => '添加仓库库位', 'view' => '查看仓库库位');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('base/StoreSeatModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }

    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('store_seat_code', 'store_seat_name', 'remark'));
        $ret = load_model('base/StoreSeatModel')->insert($data, $request['store_seat_id']);
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('store_seat_code', 'store_seat_name', 'remark'));
        $ret = load_model('base/StoreSeatModel')->update($data, $request['store_seat_id']);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/StoreSeatModel')->delete($request['store_seat_id']);
        exit_json_response($ret);
    }

}