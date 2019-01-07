<?php
require_lib('util/web_util', true);
class suspend_label {
    function do_list(array &$request, array &$response, array &$app) {
        
    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑挂起标签', 'add' => '添加挂起标签', 'view' => '查看挂起标签');
        $app['title'] = $title_arr[$app['scene']];
        $response['data']= '';
        if($app['scene'] == 'edit'){
        $ret = load_model('base/SuspendLabelModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
        }
    }
    
    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('suspend_label_code', 'suspend_label_name', 'cancel_suspend_time','remark'));
        $data['is_sys'] = 0;
        $ret = load_model('base/SuspendLabelModel')->insert($data, $request['suspend_label_id']);
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('suspend_label_name' ,'cancel_suspend_time','remark'));
        $ret = load_model('base/SuspendLabelModel')->update($data, $request['suspend_label_id']);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/SuspendLabelModel')->delete($request['suspend_label_id']);
        exit_json_response($ret);
    }
}
