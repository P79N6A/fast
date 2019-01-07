<?php

/*
 * 营销中心-报价模板
 */

class planprice {
    
    //报价模板列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑模板方案
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit' => '编辑报价模板', 'add' => '新建报价模板');
		$app['title'] = $title_arr[$app['scene']];
                if($app['scene']=="edit"){
                    $app['tpl']="market/planprice_edit_detail";
                }
                if($app['scene']=="view"){
                    $app['tpl']="market/planprice_show_detail";
                }
		 $ret = load_model('market/PlanpriceModel')->get_by_id($request['_id']);
                 $response['data'] = $ret['data'];
                
    }  
    

    //添加报价模板
    function do_add(array & $request, array & $response, array & $app) {
        $plan = get_array_vars($request, array(
            'price_name',
            'price_cpid',
            'price_stid',
            'price_note',
            'price_dot',
            'price_base',
            'price_pversion',
            'price_fulldate',
            'price_disdate',
            'price_default_limit',
        ));
        $plan['price_status'] = '1' ;
        $issue['sue_submit_time'] = date('Y-m-d H:i:s');
        $ret = load_model('market/PlanpriceModel')->insert($plan);
        exit_json_response($ret);
    }
    
    
    //编辑报价模板
    function do_edit(array & $request, array & $response, array & $app) {
        $plan = get_array_vars($request, array(
            'price_name',
            'price_cpid',
            'price_stid',
            'price_note',
            'price_dot',
            'price_base',
            'price_pversion',
            'price_fulldate',
            'price_disdate',
            'price_default_limit',
        ));
        $ret = load_model('market/PlanpriceModel')->update($plan, $request['price_id']);
        exit_json_response($ret);
    }
    
    
    function platform_shop(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑平台店铺', 'add' => '新建平台店铺');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('market/PlatformshopModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }
    
    
     //编辑云主机
    function do_platshop_edit(array & $request, array & $response, array & $app) {
        $platshop = get_array_vars($request, array(
            'pd_pt_id',
            'pd_shop_amount',
            'pd_shop_price',
        ));
        $platshop['pd_price_id'] = $request['pd_price_id'];
        $ret = load_model('market/PlatformshopModel')->update_platshop($platshop, $request['pd_id']);
        exit_json_response($ret);
    }

    //添加云主机
    function do_platshop_add(array & $request, array & $response, array & $app) {
        $platshop = get_array_vars($request, array(
            'pd_pt_id',
            'pd_shop_amount',
            'pd_shop_price',
        ));
        $platshop['pd_price_id'] = $request['pd_price_id'];
        $ret = load_model('market/PlatformshopModel')->insert_platshop($platshop);
        exit_json_response($ret);
    }
    
    //删除产品模块信息
    function do_delete_platshop(array & $request, array & $response, array & $app) {
        $ret = load_model('market/PlatformshopModel')->delete($request['pd_id']);
        exit_json_response($ret);
    }

    
    
    function get_platshop_byid(array & $request, array & $response, array & $app) {
    	$ret = load_model('market/PlatformshopModel')->get_platshop_byid($request['price_id'], array());
    	$dataset = $ret['data'];
    	//filter_fk_name($dataset['data'], array('price_id'));
    	exit_table_data_json($dataset['data'], $dataset['filter']['record_count']);
    }

    //设置报价模版状态处理。
    function set_active(array & $request, array & $response, array & $app) {
            $arr = array('enable'=>1, 'disable'=>0);
            $ret = load_model('market/PlanpriceModel')->update_planprice_active($arr[$request['type']], $request['price_id']);
            exit_json_response($ret);
    }
    
     //设置岗位状态处理。
    function set_active_enable(array & $request, array & $response, array & $app) {
            $this->set_active($request,$response,$app);
    }
    function set_active_disable(array & $request, array & $response, array & $app) {
            $this->set_active($request,$response,$app);
    }
}