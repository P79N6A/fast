<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('prm/GoodsShelfModel');

class goods_shelf {

    //品牌数据
    function get_brand() {
        //品牌  start
        $arr_brand = load_model('prm/BrandModel')->get_brand();
        $key = 0;
        foreach ($arr_brand as $value) {
            $arr_brand[$key][0] = $value['brand_code'];
            $arr_brand[$key][1] = $value['brand_name'];
            $key++;
        }
        //print_r($arr_brand);
        return $arr_brand;
        //支持多选
        //$field['data'] = $arr_brand;
        //$str = $this->_fieldtype_select_js($field, $_value);
        //品牌  end
    }

    //分类数据
//    function get_category(){
//        $arr_brand = load_model('prm/CategoryModel')->get_category();
//        $key = 0;
//        foreach ($arr_brand as $value){
//            $arr_brand[$key][0] = $value['category_code'];
//            $arr_brand[$key][1] = $value['category_name'];
//            $key++;
//        }
//        return $arr_brand;
//    }

    function do_list(array &$request, array &$response, array &$app) {

        //类别 start
        $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
        //品牌  start
        $response['brand'] = $this->get_brand();
        $response['category_code_val'] = isset($request['category_code']) ? $request['category_code'] : '';
        $response['brand_code_val'] = isset($request['brand_code']) ? $request['brand_code'] : '';
        $response['goods_code_val'] = isset($request['goods_code']) ? $request['goods_code'] : '';
        $response['goods_name_val'] = isset($request['goods_name']) ? $request['goods_name'] : '';
        $response['barcode_val'] = isset($request['barcode']) ? $request['barcode'] : '';
        //批次
        $arr = array('lof_status');
        $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['lof_status'] = $arr_lof['lof_status'];
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
    }

    function ex_list(array &$request, array &$response, array &$app) {
        //类别 start
        $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
        //品牌  start
        $response['brand'] = $this->get_brand();
        $response['category_code_val'] = isset($request['category_code']) ? $request['category_code'] : '';
        $response['brand_code_val'] = isset($request['brand_code']) ? $request['brand_code'] : '';
        $response['goods_code_val'] = isset($request['goods_code']) ? $request['goods_code'] : '';
        $response['goods_name_val'] = isset($request['goods_name']) ? $request['goods_name'] : '';
        $response['barcode_val'] = isset($request['barcode']) ? $request['barcode'] : '';
        //批次
        $arr = array('lof_status');
        $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['lof_status'] = $arr_lof['lof_status'];
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        $ret = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));

        $response['lof_status'] = $ret['lof_status'];
    }

    function a_key_unbind(array &$request, array &$response, array &$app) {
//        $app['fmt'] = 'json';
//        $m = new GoodsShelfModel();
//        $response = $m->a_key_unbind();
        
    }
    function a_key_unbind_new(array &$request, array &$response, array &$app){
        //print_r($request);
        $m = new GoodsShelfModel();
        $response = $m->a_key_unbind($request);
        exit_json_response($response);
    }
    function scanning_unbind(array &$request, array &$response, array &$app) {
        
    }

    function scanning_unbind_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new GoodsShelfModel();
        $response = $m->scanning_unbind($request['store_code'], $request['shelf_code']);
    }

    function unbind(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new GoodsShelfModel();
        $response = $m->unbind($request['goods_shelf_id']);
    }
    
    //批量解除
    function opt_unbind(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new GoodsShelfModel();
        foreach ($request['goods_shelf_id'] as $value) {
            $response = $m->unbind($value);
        }       
    }
    
    function new_unbind(array &$request, array &$response, array &$app) {

        $app['fmt'] = 'json';
        $m = new GoodsShelfModel();
        $response = $m->new_unbind($request['sku'], $request['store_code'], $request['shelf_code']);
    }

    function bind(array &$request, array &$response, array &$app) {

        $response['sku'] = $request['sku'];
        $sku_info = load_model('goods/SkuCModel')->get_sku_info($response['sku'], array('barcode'));
        $response['barcode'] = $sku_info['barcode'];



        if (isset($request['lof_no']) && $request['lof_no'] != '') {
            $response['lof_no'] = isset($request['lof_no']) ? $request['lof_no'] : '';
        } else {
            $sys_lof = load_model("prm/GoodsLofModel")->get_sys_lof();
            $response['lof_no'] = $sys_lof['lof_no'];
        }


        $ret = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));

        $response['lof_status'] = $ret['lof_status'];
    }

    function bind_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new GoodsShelfModel();
        $response = $m->multi_bind($request['goods_inv_id'], $request['shelf_code_list'], $request['skuCode'], $request['batch_number'],$request['shelf_info']);
    }

    function import(array &$request, array &$response, array &$app) {

        $ret = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));

        $response['lof_status'] = $ret['lof_status'];
    }
    function import_bygoods_code(array &$request, array &$response, array &$app) {

        $ret = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));

        $response['lof_status'] = $ret['lof_status'];
    }

    function import_action(array &$request, array &$response, array &$app) {
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        //$arrExcel = $this->read_csv($file);

        $m = new GoodsShelfModel();
        $request['bischecked'] = isset($request['bischecked']) ? $request['bischecked'] : 0;
        !empty($request['type'])?$type=$request['type']:$type='';
        $ret = $m->import_goods_shelf($file, $request['bischecked'],$type);
        $response = $ret;
    }

    function read_csv($file) {
        //    $key_arr = array('0'=>'sku','1'=>'num','lof_no'=>,);
        $file = fopen($file, "r");
        $i = 0;
        $arr = array();
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                // $row = $this->tran_csv($row);
                if (!empty($row[0])) {
                    $arr[] = $row;
                }
            }
            $i++;
        }
        fclose($file);
        return $arr;
    }

}
