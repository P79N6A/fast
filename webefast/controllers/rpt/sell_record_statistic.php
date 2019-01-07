<?php
/**
 * 销售订单统计分析
 */
class sell_record_statistic {
    
    function analyse(array & $request, array & $response, array & $app){
//    	$response['category'] = load_model('prm/CategoryModel')->get_category_trees();
//		$response['brand'] = $this->get_purview_brand();
    }
    
        
    // 销售订单
    function sell_record(array &$request, array &$response, array &$app) {
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
        $app['page'] = 'NULL';
    }
    
     // 店铺和编码
    function shop_goods_code(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
     // 平台，店铺和编码
    function sale_channel_shop_goods_code(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
    // 订单
    function record(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
         // 平台和仓库
    function sale_channel_store(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
         // 店铺和仓库
    function shop_store(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
             // 平台，店铺和仓库
    function sale_channel_shop_store(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
    
    
    
}