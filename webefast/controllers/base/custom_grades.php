<?php

require_lib('util/web_util', true);

class custom_grades {

    function do_list(array & $request, array & $response, array & $app) {
        
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('base/CustomGradesModel')->get_by_id($request['_id']);
            $response['app_scene'] = 'edit';
        } else {
            $response['app_scene'] = 'add';
            $ret['data']['grade_code'] = load_model('base/CustomGradesModel')->create_fast_bill_sn();
        }
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
    }

    function do_edit(array & $request, array & $response, array & $app) {
        $custom_grade_info = get_array_vars($request, array('grade_name', 'remark'));
        $ret = load_model('base/CustomGradesModel')->update($custom_grade_info, $request['grade_id']);
        exit_json_response($ret);
    }

    function do_add(array & $request, array & $response, array & $app) {
        $custom_grade_info = get_array_vars($request, array('grade_code', 'grade_name', 'remark'));
        $ret = load_model('base/CustomGradesModel')->insert($custom_grade_info);
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('base/CustomGradesModel')->delete_grades($request['grade_code']);
        exit_json_response($ret);
    }

    function do_add_custom(array & $request, array & $response, array & $app){
        $ret = load_model('base/CustomGradesModel')->insert_custom($request);
        exit_json_response($ret);
    }
    
    function delete_custom(array & $request, array & $response, array & $app){
        $ret = load_model('base/CustomGradesModel')->delete_custom($request);
        exit_json_response($ret);
    }
    
    function delete_grade_detail(array & $request, array & $response, array & $app){
        $ret = load_model('base/CustomGradesModel')->delete_grade_detail($request['id']);
        exit_json_response($ret);
    }
}
