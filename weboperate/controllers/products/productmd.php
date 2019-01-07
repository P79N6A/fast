<?php

/*
 * 系统档案-产品信息类
 */
require_lib ( 'util/web_util', true );
class productmd {
    
    //产品成员列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建产品成员显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑产品成员', 'add'=>'新建产品成员');
		$app['title'] = $title_arr[$app['scene']];
		$ret = load_model('products/ProductmdModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
                
    }
    
    //编辑产品成员信息数据处理。
    function do_edit(array & $request, array & $response, array & $app) {
		$prouctmd = get_array_vars($request, 
                            array('pcm_user',
                                'pcm_user_post',
                                ));
		$ret = load_model('products/ProductmdModel')->update($prouctmd, $request['pcm_id']);
		exit_json_response($ret);
	}
    //添加产品成员信息数据处理。    
    function do_add(array & $request, array & $response, array & $app) {
		$prouctmd = get_array_vars($request, 
                            array('pcm_user',
                                'pcm_user_post',
                                'pcm_cp_id',
                                ));
		$ret = load_model('products/ProductmdModel')->insert($prouctmd);
		exit_json_response($ret);
	}
    
    //删除产品成员信息
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('products/ProductmdModel')->delete($request['pcm_id']);
        exit_json_response($ret);
    }
}