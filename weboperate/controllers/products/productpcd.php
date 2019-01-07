<?php

/*
 * 产品中心-产品补丁-补丁sql明细
 */
require_lib ( 'util/web_util', true );
class productpcd {
    
    //补丁sql明细列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建补丁SQL明细显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
//        print_r($request);die;
		$title_arr = array('edit'=>'编辑SQL明细', 'add'=>'新建SQL明细');
		$app['title'] = $title_arr[$app['scene']];
                if($app['scene']=="add"){
                    $rdata['version_no'] =$request['v_no'];
                    $rdata['version_patch'] =$request['v_path'];
                    $response['data'] = $rdata;
                }else{
                    $ret = load_model('products/ProductpcdModel')->get_by_id($request['_id']);
                    $response['data'] = $ret['data'];
                }
    }
    
    //编辑补丁SQL
    function do_edit(array & $request, array & $response, array & $app) {
		$prouctpcd = get_array_vars($request, 
                            array(
                                'content',
                                'is_exec',
                                'task_sn',
                                ));
                $prouctpcd['version_no']=$request['hd_version_no'];
                $prouctpcd['version_patch']=$request['hd_version_patch'];
		$ret = load_model('products/ProductpcdModel')->update($prouctpcd, $request['id']);
		exit_json_response($ret);
	}
    //添加补丁SQL。    
    function do_add(array & $request, array & $response, array & $app) {
		$prouctpcd = get_array_vars($request, 
                            array(
                                'content',
                                'is_exec',
                                'task_sn',
                                ));
                $prouctpcd['version_no']=$request['hd_version_no'];
                $prouctpcd['version_patch']=$request['hd_version_patch'];
//                print_r($prouctpcd);die;
		$ret = load_model('products/ProductpcdModel')->insert($prouctpcd);
		exit_json_response($ret);
	}
    
    //删除补丁SQL
    function do_delete_sql(array & $request, array & $response, array & $app) {
        $ret = load_model('products/ProductpcdModel')->delete($request['id']);
        exit_json_response($ret);
    }
}