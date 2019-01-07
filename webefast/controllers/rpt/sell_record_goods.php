<?php

/**
 * 销售商品分析
 */
class sell_record_goods {
    
    function analyse(array & $request, array & $response, array & $app){
  	$response['category'] = load_model('prm/CategoryModel')->get_category_trees();
        $response['brand'] = $this->get_purview_brand();
    }
    
    function get_purview_brand(){
        //品牌  start
        $arr_brand = load_model('prm/BrandModel')->get_purview_brand();

        $key = 0;
        foreach ($arr_brand as $value){
                $arr_brand[$key][0] = $value['brand_code'];
                $arr_brand[$key][1] = $value['brand_name'];
                $key++;
        }
        return $arr_brand;

    }
    
    
     
    // 销售订单
    function sell_record(array &$request, array &$response, array &$app) {
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        if($property_power) {
            $response['proprety'] = load_model('prm/GoodsPropertyModel')->get_property_val('property_code,property_val_title,property_val');
        }
        $app['page'] = 'NULL';
    }
    
    // 销售渠道
    function sale_channel(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    // 商店
    function shop(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    // 仓库
    function store(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    // 品牌
    function brand(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
    // 种类
    function category(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
    // 季节
    function season(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
    // 商品编码
    function goods_code(array &$request, array &$response, array &$app) {
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        if($property_power) {
            $response['proprety'] = load_model('prm/GoodsPropertyModel')->get_property_val('property_code,property_val_title,property_val');
        }        
        $app['page'] = 'NULL';
    }
   // 商品条形码
    function barcode(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
        // 平台和店铺
    function sale_channel_shop(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
      // 平台和编码
    function sale_channel_goods_code(array &$request, array &$response, array &$app) {
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        if($property_power) {
            $response['proprety'] = load_model('prm/GoodsPropertyModel')->get_property_val('property_code,property_val_title,property_val');
        }        
        $app['page'] = 'NULL';
    }
    
     // 店铺和编码
    function shop_goods_code(array &$request, array &$response, array &$app) {
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        if($property_power) {
            $response['proprety'] = load_model('prm/GoodsPropertyModel')->get_property_val('property_code,property_val_title,property_val');
        }
        $app['page'] = 'NULL';
    }
     // 平台，店铺和编码
    function sale_channel_shop_goods_code(array &$request, array &$response, array &$app) {
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        if($property_power) {
            $response['proprety'] = load_model('prm/GoodsPropertyModel')->get_property_val('property_code,property_val_title,property_val');
        }        
        $app['page'] = 'NULL';
    }
    
    //年份
    function years(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
    
    
    
}