<?php

/*
 * 系统档案-产品信息类
 */
require_lib ( 'util/web_util', true );
class Productinfo {
    
    //客户档案列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑产品档案显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑产品信息', 'add'=>'新建产品');
		$app['title'] = $title_arr[$app['scene']];
                if($app['scene']=="edit"){
                    $app['tpl']="products/productinfo_detail_edit";
                }
                if($app['scene']=="view"){
                    $app['tpl']="products/productinfo_detail_show";
                }
		$ret = load_model('products/ProductModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
                
    }
    
    //编辑岗位信息数据处理。
    function product_edit(array & $request, array & $response, array & $app) {
		$prouct = get_array_vars($request, 
                            array('cp_code', 
                                'cp_name',
                                'cp_en_name',
                                'cp_jc',
                                'cp_order',
                                'cp_maintain',
                                'cp_autoacc',
                                'cp_memo',
                                ));
		$ret = load_model('products/ProductModel')->update($prouct, $request['cp_id']);
		exit_json_response($ret);
	}
    //添加岗位信息数据处理。    
    function product_add(array & $request, array & $response, array & $app) {
		$prouct = get_array_vars($request, 
                            array('cp_code', 
                                'cp_name',
                                'cp_en_name',
                                'cp_jc',
                                'cp_order',
                                'cp_maintain',
                                'cp_memo',
                                ));
		$ret = load_model('products/ProductModel')->insert($prouct);
		exit_json_response($ret);
	}
    function set_cp_maintain(array & $request, array & $response, array & $app) {
        $status = $request['status']==1?0:1;
        $response = load_model('products/ProductModel')->update_maintain($request['cp_id'],$status);
    }
    
    
}