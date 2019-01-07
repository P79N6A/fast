<?php

/*
 * 客户中心-店铺档案
 */

class Shopinfo {
    
    //店铺店铺列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑店铺显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑店铺信息', 'add'=>'新建店铺');
		$app['title'] = $title_arr[$app['scene']];
		$ret = load_model('clients/ShopModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
	}
    
    //编辑店铺信息数据处理。
    function shop_edit(array & $request, array & $response, array & $app) {
		$shop = get_array_vars($request, 
                            array(
                                'sd_name',
                                'sd_kh_id',
                                'sd_fzr',
                                'sd_lxfs',
                                'sd_bz',
                                'sd_pt_id',
                                'sd_agent',
                                'sd_servicer',
                                'sd_login_name',
                                'sd_email',
                                'sd_pt_shoptype',
                                ));
                //$ret = load_model('sys/UserModel_ex')->get_by_id(CTX()->get_session("user_id"));
                $shop['sd_updateuser'] = CTX()->get_session("user_id");
                $shop['sd_updatedate'] = date('Y-m-d H:i:s');
		$ret = load_model('clients/ShopModel')->update($shop, $request['sd_id']);
		exit_json_response($ret);
	}
    //添加岗位信息数据处理。    
    function shop_add(array & $request, array & $response, array & $app) {
		$shop = get_array_vars($request, 
                        array( 
                                'sd_name',
                                'sd_kh_id',
                                'sd_fzr',
                                'sd_lxfs',
                                'sd_bz',
                                'sd_pt_id',
                                'sd_agent',
                                'sd_servicer',
                                'sd_login_name',
                                'sd_email',
                                'sd_pt_shoptype',
                                'sd_nick'
                                ));
                //$ret = load_model('sys/UserModel_ex')->get_by_id(CTX()->get_session("user_id"));
                $shop['sd_code'] = uniqid();
                $shop['sd_createuser'] = CTX()->get_session("user_id");
                $shop['sd_createdate'] = date('Y-m-d H:i:s');
		$ret = load_model('clients/ShopModel')->insert($shop);
		exit_json_response($ret);
	}
        
    //通过选择平台类型得到不同的平台店铺类型
    function do_getshop_type(array & $request, array & $response, array & $app){
        $pt_id=$request['ptid'];
        $ret = load_model('clients/ShopModel')->getshop_type($pt_id);
        exit_json_response($ret);
    }
    
    
    //淘宝API接口，数据推送绑定
    function set_databind(array & $request, array & $response, array & $app){
        $arr = array('databind'=>1, 'nodatabind'=>0);
        $ret = load_model('clients/ShopModel')->update_shop_databind($arr[$request['type']], $request['sd_id']);
        exit_json_response($ret);
    }

    function set_shop_databind(array & $request, array & $response, array & $app) {
        $this->set_databind($request,$response,$app);
    }
    function set_shop_nodatabind(array & $request, array & $response, array & $app) {
        $this->set_databind($request,$response,$app);
    }
}