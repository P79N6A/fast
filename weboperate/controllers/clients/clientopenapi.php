<?php

/*
 * 服务中心中心-客户KEY
 */

class Clientopenapi {
    
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑店铺显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'客户KEY信息', 'add'=>'客户KEY');
		$app['title'] = $title_arr[$app['scene']];
		$ret = load_model('clients/ShopModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
    } 
}