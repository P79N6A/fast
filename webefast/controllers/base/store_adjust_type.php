<?php
require_lib('util/web_util', true);
class store_adjust_type {
    function do_list(array &$request, array &$response, array &$app) {

    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑库存调整类型', 'add' => '添加库存调整类型', 'view' => '查看库存调整类型');
        $app['title'] = $title_arr[$app['scene']];
        if(isset($request['_id']) && !empty($request['_id'])){
            $ret = load_model('base/StoreAdjustTypeModel')->get_row(array('record_type_id' => $request['_id']));
            $response['data'] = $ret['data'];
        }
        $response['data']['record_type_property'] = '8';
    }

    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('record_type_code', 'record_type_name', 'remark','record_type_property'));
        $ret = load_model('base/StoreAdjustTypeModel')->insert($data, $request['record_type_id']);
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('record_type_name', 'remark','record_type_property'));
        $ret = load_model('base/StoreAdjustTypeModel')->update($data, $request['record_type_id']);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/StoreAdjustTypeModel')->delete($request['record_type_id']);
        exit_json_response($ret);
    }

}