<?php
require_lib('util/web_util', true);
class picking {
    function do_list(array &$request, array &$response, array &$app) {

    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑模板', 'add' => '添加模板', 'view' => '查看模板');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('base/PickingModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }

    function do_add(array &$request, array &$response, array &$app) {
        $user = get_array_vars($request, array('picking_name', 'picking_desc', 'picking_content', 'ispublic'));
        $ret = load_model('base/PickingModel')->insert($user);
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $user = get_array_vars($request, array('picking_name', 'picking_desc', 'picking_content', 'ispublic'));
        $ret = load_model('base/PickingModel')->update($user, $request['id']);
        exit_json_response($ret);
    }

    function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('base/PickingModel')->update_active($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/PickingModel')->do_delete($request['id']);
        exit_json_response($ret);
    }
}