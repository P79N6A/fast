<?php

/*
 * 产品中心-增值服务类别
 */

class Valueservice_cat {
    
    //增值服务类别
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑增值服务类别的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑增值服务类别', 'add'=>'新建增值服务类别');
		$app['title'] = $title_arr[$app['scene']];
		$ret = load_model('market/Value_catModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
	}
    
    //编辑增值服务类别数据处理。
    function do_edit(array & $request, array & $response, array & $app) {
		$values = get_array_vars($request, 
                            array('vc_name',
                                'vc_order',
                                'vc_cp_id',
                                'vc_bz',
                                ));
		$ret = load_model('market/Value_catModel')->update($values, $request['vc_id']);
                if($ret['status']){
                    load_model('basedata/RdsDataModel')->update_rds_all('osp_valueserver_category');
                }
		exit_json_response($ret);
	}
    //添加增值服务类别数据处理。    
    function do_add(array & $request, array & $response, array & $app) {
		$values = get_array_vars($request, 
                            array('vc_code',
                                'vc_name', 
                                'vc_order',
                                 'vc_cp_id',
                                'vc_bz',
                                ));
		$ret = load_model('market/Value_catModel')->insert($values);
                if($ret['status']){
                    load_model('basedata/RdsDataModel')->update_rds_all('osp_valueserver_category');
                }
		exit_json_response($ret);
	}
    
    //设置增值服务类别状态处理。
    function set_active(array & $request, array & $response, array & $app) {
		$arr = array('enable'=>1, 'disable'=>0);
		$ret = load_model('market/Value_catModel')->update_value_enable($arr[$request['type']], $request['vc_id']);
		exit_json_response($ret);
    }
    
     //设置增值服务类别状态处理。
    function set_active_enable(array & $request, array & $response, array & $app) {
            $this->set_active($request,$response,$app);
    }
    function set_active_disable(array & $request, array & $response, array & $app) {
            $this->set_active($request,$response,$app);
    }
    
}