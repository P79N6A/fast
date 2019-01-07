<?php
require_lib('util/web_util', true);
class return_reason {
    function do_list(array &$request, array &$response, array &$app) {

    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑商品退货原因', 'add' => '添加商品退货原因', 'view' => '查看商品退货原因');
        $app['title'] = $title_arr[$app['scene']];
        $response['data'] = '';
        if($app['scene'] == 'edit'){
        $ret = load_model('base/ReturnReasonModel')->get_row(array('return_reason_id' => $request['_id']));
        $response['data'] = $ret['data'];
        }
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('return_reason_name', 'return_reason_type', 'is_active', 'remark'));
        $ret = load_model('base/ReturnReasonModel')->update($data, $request['return_reason_id']);
        exit_json_response($ret);
    }

    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('return_reason_code', 'return_reason_name', 'return_reason_type', 'is_active', 'remark'));
        $data['is_sys'] = 1;
        $ret = load_model('base/ReturnReasonModel')->insert($data);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/ReturnReasonModel')->delete($request['return_reason_id']);
        exit_json_response($ret);
    }
    
    /**
     * 启用/停用开关
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function active_switch(array &$request, array &$response, array &$app) {
        $ret = load_model('base/ReturnReasonModel')->update_active($request['is_active'], $request['return_reason_id']);
        exit_json_response($ret);
    }
}