<?php

/*
 * 基础数据-平台列表
 */

class platform {
    
    //平台列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑平台列表显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑平台', 'add'=>'新建平台');
                $app['title'] = $title_arr[$app['scene']];
                if($app['scene']=="edit"){
                    $app['tpl']="basedata/platform_detail_edit";
                }
                if($app['scene']=="view"){
                    $app['tpl']="basedata/platform_detail_show";
                }
                
		$ret = load_model('basedata/PlatformModel')->get_by_id($request['_id']);
                $response['data'] = $ret['data'];
	}
    //编辑平台
    function do_edit(array & $request, array & $response, array & $app) {
		$platform = get_array_vars($request, 
                            array( 
                                'pt_code',
                                'pt_name',
                                'pt_offurl',
                                'pt_techurl',
                                'pt_serurl',
                                'pt_state',
                                'pt_bz',
                                'pt_logo',
                                'pt_pay_type',
                                ));
		$ret = load_model('basedata/PlatformModel')->update($platform, $request['pt_id']);
		exit_json_response($ret);
	}
        
    //添加平台。    
    function do_add(array & $request, array & $response, array & $app) {
		$platform = get_array_vars($request, 
                            array( 
                                'pt_code',
                                'pt_name',
                                'pt_offurl',
                                'pt_techurl',
                                'pt_serurl',
                                'pt_state',
                                'pt_bz',
                                'pt_logo',
                                'pt_pay_type'
                                ));
		$ret = load_model('basedata/PlatformModel')->insert($platform);
		exit_json_response($ret);
    }
    
    function shop_type(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑平台店铺类型', 'add' => '新建平台店铺类型');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('basedata/PlatformModel')->get_platshop_type($request['_id']);
        $response['data'] = $ret['data'];
    }
    
    //删除平台列表-店铺类型
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('basedata/PlatformModel')->delete($request['cm_id']);
        exit_json_response($ret);
    }
    
    
    function do_platshop_add(array & $request, array & $response, array & $app) {
        $shop = get_array_vars($request, array(
            'pd_pt_id',
            'pd_shop_type',
        ));
        $ret = load_model('basedata/PlatformModel')->insert_platshop_type($shop);
        exit_json_response($ret);
    }
    
    function do_platshop_edit(array & $request, array & $response, array & $app) {
        $shop = get_array_vars($request, array(
            'pd_shop_type',
        ));
        $ret = load_model('basedata/PlatformModel')->update_platshop_type($shop, $request['pd_id']);
        exit_json_response($ret);
    }
    
    function do_platshop_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('basedata/PlatformModel')->delete_platshop_type($request['pd_id']);
        exit_json_response($ret);
    }
}