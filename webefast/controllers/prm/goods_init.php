<?php

/**
 * 商品初始化
 * @author FBB
 *
 */
require_lib('util/web_util', true);
require_model('api/taobao/GoodsModel', true);

class goods_init {

    function do_list(array & $request, array & $response, array & $app) {
        
    }

    function check_goods_code(array & $request, array & $response, array & $app) {
        $api_goods_sku_id_str = join("','", $request['api_goods_sku_id']);
        $ret = load_model('prm/GoodsInitModel')->check_goods_code($api_goods_sku_id_str);
        exit_json_response($ret);
    }

    //批量商品初始化
    function opt_batch(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $msgFaild = '';
        $mdlGoods = new GoodsModel();
        foreach ($request['api_goods_sku_id'] as $api_goods_sku_id) {
            if (empty($api_goods_sku_id)) {
                continue;
            }
            $func = $request['type'];
            $ret = $mdlGoods->$func($api_goods_sku_id);
            $msg = '';
            if ($ret['status'] == '1') {
                $msg = '商品初始化成功';
            } else {
                $msgFaild .= $ret['message'] . ',<br/>';
            }
        }
        if (!empty($msgFaild)) {
            $msg .= sprintf("商品单号:<br/> %s", rtrim($msgFaild, ','));
        }
        $response = array('status' => 1, 'message' => $msg);
    }
    
    function stock_init(array &$request, array &$response, array &$app) {
        $response['store'] = load_model('base/StoreModel')->get_purview_store();
        if($request['type'] == 'batch'){
            $response['data'] = load_model('prm/GoodsInitModel')->stock_init_sum($request);
        }else{
            $response['data'] = load_model('prm/GoodsInitModel')->stock_init_sum();
        }
        $response['type'] = $request['type'];
        $response['api_goods_sku_id'] = $request['api_goods_sku_id'];
    }

    function do_stock_init(array &$request, array &$response, array &$app) {
        $request['is_add_time'] = date('Y-m-d H:i:s');
        $request['take_stock_time'] = date('Y-m-d H:i:s');
        $request['record_code'] = load_model('stm/TakeStockRecordModel')->create_fast_bill_sn();
        $ret = load_model('prm/GoodsInitModel')->do_stock_init($request);
        exit_json_response($ret);
    }
    
    function auto_goods_init(array &$request, array &$response, array &$app){
        $ret = load_model('prm/GoodsInitModel')->auto_goods_init();
        
        $response['status'] = 1;
	
    }
}
