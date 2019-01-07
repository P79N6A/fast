<?php

/**
 * 商品控制器相关业务
 * @author dfr
 *
 */
require_lib('util/web_util', true);

class goods_manage {

    function do_list(array & $request, array & $response, array & $app) {
        
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('fx/GoodsManageModel')->get_by_id($request['_id']);
            $response['app_scene'] = 'edit';
        } else {
            $response['app_scene'] = 'add';
            $ret['data']['goods_line_code'] = load_model('fx/GoodsManageModel')->create_fast_bill_sn();
        }
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
    }

    function do_edit(array & $request, array & $response, array & $app) {
        $goods_manager_info = get_array_vars($request, array('goods_line_name', 'id'));
        $ret = load_model('fx/GoodsManageModel')->do_edit($goods_manager_info, $request['id']);
        exit_json_response($ret);
    }

    function do_add(array & $request, array & $response, array & $app) {
        $info = get_array_vars($request, array('goods_line_code', 'goods_line_name'));
        $ret = load_model('fx/GoodsManageModel')->insert($info);
        exit_json_response($ret);
    }

    function do_add_goods(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/GoodsManageModel')->insert_goods($request);
        exit_json_response($ret);
    }

    function delete_all_goods(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/GoodsManageModel')->delete_all_goods($request['goods_line_code']);
        exit_json_response($ret);
    }

    function do_delete_goods(array &$request, array &$response, array &$app){
        $ret = load_model('fx/GoodsManageModel')->do_delete_goods($request['goods_id'],$request['goods_line_code']);
        exit_json_response($ret);
    }
    
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/GoodsManageModel')->do_delete($request['goods_line_code']);
        exit_json_response($ret);
    }

    function import_goods(array &$request, array &$response, array &$app) {
        
    }

    //会员导入
    function do_import_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
            return $response;
        }
        $ret = load_model('fx/GoodsManageModel')->do_import_goods($file, $request['goods_line_code']);
        $response = $ret;
    }

    function add_custom_grade(array &$request, array &$response, array &$app){
        $response['grade_code'] = load_model('base/CustomGradesModel')->get_all_grades(1);
    }
    
    function do_add_custom_grade(array &$request, array &$response, array &$app){
        $ret = load_model('fx/GoodsManageModel')->insert_custom_grade($request);
        exit_json_response($ret);
    }
    
    function delete_all_custom_grade(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/GoodsManageModel')->delete_all_custom_grade($request['goods_line_code']);
        exit_json_response($ret);
    }

    function delete_custom_grade(array &$request, array &$response, array &$app){
        $ret = load_model('fx/GoodsManageModel')->delete_custom_grade($request['price_custom_grade_id'],$request['goods_line_code']);
        exit_json_response($ret);
    }
    
    function add_custom(array &$request, array &$response, array &$app){
    }
    
    function do_add_custom(array &$request, array &$response, array &$app){
        $ret = load_model('fx/GoodsManageModel')->do_add_custom($request);
        exit_json_response($ret);
    }
    
    function delete_custom(array &$request, array &$response, array &$app){
        $ret = load_model('fx/GoodsManageModel')->delete_custom($request['price_custom_id'],$request['goods_line_code']);
        exit_json_response($ret);
    }
    
    function delete_all_custom(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/GoodsManageModel')->delete_all_custom($request['goods_line_code']);
        exit_json_response($ret);
    }
    
    function import_custom(array &$request, array &$response, array &$app) {
        
    }

    //会员导入
    function do_import_custom(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
            return $response;
        }
        $ret = load_model('fx/GoodsManageModel')->do_import_custom($file, $request['goods_line_code']);
        $response = $ret;
    }
    
}
