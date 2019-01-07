<?php
require_lib('util/web_util', true);
class return_label {
    function do_list(array &$request, array &$response, array &$app) {

    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑退单标签', 'add' => '添加退单标签');
        $app['title'] = $title_arr[$app['scene']];
        if (isset($request['_id']) && $request['_id'] != '') {
        $ret = load_model('base/ReturnLabelModel')->get_by_id($request['_id']);
        }
        $response['data'] = isset($ret['data'])?$ret['data']:'';
    }
    
    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('return_label_code', 'return_label_name', 'remark'));
        $ret = load_model('base/ReturnLabelModel')->insert($data, $request['return_label_id']);
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('return_label_name', 'remark'));
        $ret = load_model('base/ReturnLabelModel')->update($data, $request['return_label_id']);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/ReturnLabelModel')->delete($request['return_label_id']);
        exit_json_response($ret);
    }

}