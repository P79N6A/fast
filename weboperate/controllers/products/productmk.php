<?php

/*
 * 系统档案-产品信息类
 */
require_lib ( 'util/web_util', true );
class productmk {
    
    //产品模块列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建产品模块显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑产品模块', 'add'=>'新建产品模块');
		$app['title'] = $title_arr[$app['scene']];
		$ret = load_model('products/ProductmkModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
                
    }
    
    //编辑产品模块信息数据处理。
    function do_edit(array & $request, array & $response, array & $app) {
		$prouctmk = get_array_vars($request, 
                            array('pm_name',
                                'pm_en_name',
                                'pm_jc',
                                'pm_memo',
                                ));
		$ret = load_model('products/ProductmkModel')->update($prouctmk, $request['pm_id']);
		exit_json_response($ret);
	}
    //添加产品模块信息数据处理。    
    function do_add(array & $request, array & $response, array & $app) {
		$prouctmk = get_array_vars($request, 
                            array('pm_name',
                                'pm_en_name',
                                'pm_jc',
                                'pm_memo',
                                'pm_cp_id'
                                ));
		$ret = load_model('products/ProductmkModel')->insert($prouctmk);
		exit_json_response($ret);
	}
    
    //删除产品模块信息
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('products/ProductmkModel')->delete($request['pm_id']);
        exit_json_response($ret);
    }
}