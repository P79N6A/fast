<?php

/*
 * 基础数据-平台列表
 */

class cloud {
    
    //服务商列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑平台列表显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑供应商', 'add'=>'新建供应商');
		$app['title'] = $title_arr[$app['scene']];
                if($app['scene']=="edit"){
                    $app['tpl']="basedata/cloud_detail_edit";
                }
                if($app['scene']=="view"){
                    $app['tpl']="basedata/cloud_detail_show";
                }
		$ret = load_model('basedata/CloudModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
                
    }    
        
        
    //编辑云服务商
    function do_edit(array & $request, array & $response, array & $app) {
		$cloud = get_array_vars($request, 
                            array( 
                                'cd_name',
                                'cd_official',
                                'cd_note',
                                ));
		$ret = load_model('basedata/CloudModel')->update($cloud, $request['cd_id']);
		exit_json_response($ret);
	}
        
    //添加云服务商
    function do_add(array & $request, array & $response, array & $app) {
		$cloud = get_array_vars($request, 
                            array( 
                                'cd_name',
                                'cd_official',
                                'cd_note',
                                ));
		$ret = load_model('basedata/CloudModel')->insert($cloud);
		exit_json_response($ret);
    }    
    
    //通过服务商ID获取云主机类型
    function do_getcloud_server(array & $request, array & $response, array & $app){
        $cd_id=$request['cdid'];
        $ret = load_model('basedata/CloudModel')->getcloudmd($cd_id,"1");
        exit_json_response($ret);
    }
    
    //获取主机配置信息
    function get_host_info(array & $request, array & $response, array & $app) {
        $ret = load_model('basedata/CloudModel')->get_hosts($request['cdmdid']);
        exit_json_response($ret);
    }
    
    //通过服务商ID获取云数据库类型
    function do_getcloud_db(array & $request, array & $response, array & $app){
        $cd_id=$request['cdid'];
        $ret = load_model('basedata/CloudModel')->getdbmd($cd_id,"2");
        exit_json_response($ret);
    }
    
    //获取db配置信息
    function get_db_info(array & $request, array & $response, array & $app) {
        $ret = load_model('basedata/CloudModel')->get_dbs($request['cdmdid']);
        exit_json_response($ret);
    }
    
}