<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('prm/GoodsPropertyModel');

class goods_property
{

    function do_list(array &$request, array &$response, array &$app) {
    	
    	$m = new GoodsPropertyModel();
        $response = $m->get_all_rows();
    }

    //保存明细
    function opt_save_detail(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new GoodsPropertyModel();
        $response = $mdl->opt_save_detail($request['property_set_id'],$request['property_val_title']);
    }

    function goods_prop(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new GoodsPropertyModel();
        $response = $mdl->get_prop_data($request['goods_code'],$request['type']);
    }

    function save_goods_prop(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new GoodsPropertyModel();
        $response = $mdl->save_goods_prop($request['goods_code'],$request);
    }
    
}