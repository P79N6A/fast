<?php

/*
 * 产品中心-自动服务管理
 */

class Autotask_manage {
    
    //自动服务列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑自动服务页面显示
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑自动服务', 'add'=>'新建自动服务');
		$app['title'] = $title_arr[$app['scene']];
		$ret = load_model('products/AutomanageModel')->get_autotask_id($request['_id']);
		$response['data'] = $ret['data'];
	}
    
    //编辑自动服务
    function do_atask_edit(array & $request, array & $response, array & $app) {
		$atask = get_array_vars($request, 
                            array(
                                'asa_vm_id',
                                'asa_rds_id',
                                'asa_product_version',
                                'asa_cp_id',
                                'asa_cp_version_id',
                                ));
                $atask['asa_updatedate'] = date('Y-m-d H:i:s');
		$ret = load_model('products/AutomanageModel')->update($atask, $request['asa_id']);
                if($ret['status']){
                    load_model('basedata/RdsDataModel')->update_kh_data(0,$request['asa_rds_id'],'osp_vmextmanage_ver');
                }
		exit_json_response($ret);
	}
    //添加自动服务    
    function do_atask_add(array & $request, array & $response, array & $app) {
		$atask = get_array_vars($request, 
                            array(
                                'asa_vm_id',
                                'asa_rds_id',
                                'asa_product_version',
                                'asa_cp_id',
                                'asa_cp_version_id',
                                ));
                $atask['asa_createdate'] = date('Y-m-d H:i:s');
		$ret = load_model('products/AutomanageModel')->insert($atask);
		if($ret['status']){
                    load_model('basedata/RdsDataModel')->update_kh_data(0,$request['asa_rds_id'],'osp_vmextmanage_ver');
                }
                exit_json_response($ret);
	}
        
}