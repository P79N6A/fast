<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class goods_adjust_price {

    function do_list(array &$request, array &$response, array &$app) {
        
    }
    
    function detail(array &$request, array &$response, array &$app) {
        //生成单据号
        $response['data']['record_code'] = load_model('fx/GoodsAdjustPriceModel')->create_fast_bill_sn();
        $response['app_scene'] = 'add';
        //分销商分类
        $response['custom_grades'] = load_model('base/CustomGradesModel')->get_all_grades(1);
    }
    
    function do_add(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('record_code', 'adjust_price_object', 'custom_code','custom_grades','start_time','end_time','settlement_price_type','settlement_rebate'));
        $ret = load_model('fx/GoodsAdjustPriceModel')->create_goods_adjust_record($params);
	exit_json_response($ret);
    }
    
    function view(array &$request, array &$response, array &$app) {
        $response['data'] = load_model('fx/GoodsAdjustPriceModel')->get_by_id($request['id']);
    }
    
    function update_active(array &$request, array &$response, array &$app) {
	$arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('fx/GoodsAdjustPriceModel')->update_active($request['id'], $arr[$request['type']]);
	exit_json_response($ret);
    }
    
    function do_add_detail(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/GoodsAdjustPriceDetailModel')->do_add_detail($request['id'],$request['data']);
	exit_json_response($ret);
    }
    
    function do_delete_detail(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/GoodsAdjustPriceDetailModel')->do_delete_detail($request['id'], $request['detail_id'], $request['barcode']);
	exit_json_response($ret);
    }
    
    function edit_rebate_or_money(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/GoodsAdjustPriceDetailModel')->edit_rebate_or_money($request['params'], $request['settlement_type']);
	exit_json_response($ret);
    }
    
    function do_edit_detail(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('adjust_price_detail_id', 'money', 'rebate', 'pid'));
        $ret = load_model('fx/GoodsAdjustPriceDetailModel')->do_edit_detail($params);
	exit_json_response($ret);
    }
    
    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/GoodsAdjustPriceModel')->do_delete($request['id']);
	exit_json_response($ret);
    }
    
    function importGoods(array &$request, array &$response, array &$app) {
        
    }
    
    function import_goods_upload(array &$request, array &$response, array &$app) {
        //导入商品
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        //1-按商品条形码导入2-按商品编码导入
        $op_type = isset($request['op_type']) ? $request['op_type'] : 1;
        if($op_type == 1){
            $ret = load_model('fx/GoodsAdjustPriceDetailModel')->imoprt_detail($request['id'],$file);
        }else{
            $ret = load_model('fx/GoodsAdjustPriceDetailModel')->imoprt_detail_goods($request['id'],$file);
        }
        $response = $ret;
    }
}
