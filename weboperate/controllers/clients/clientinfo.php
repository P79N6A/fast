<?php

/*
 * 系统档案-岗位信息类
 */

class Clientinfo {
    
    //客户档案列表
    function do_list(array & $request, array & $response, array & $app) {
    
        
    }
    
    //新建岗位、编辑岗位显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑客户信息', 'add'=>'新建客户');
		$app['title'] = $title_arr[$app['scene']];
		$ret = load_model('clients/ClientModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
	}
    
    //编辑岗位信息数据处理。
    function client_edit(array & $request, array & $response, array & $app) {
		$client = get_array_vars($request, 
                            array(
                                'kh_name',
                                'kh_place',
                                'star_code',
                                'star_password',
                                'kh_address',
                                'kh_tel',
                                'kh_email',
                                'kh_web',
                                'kh_itphone',
                                'kh_itname',
                                'kh_placeuser',
                                'kh_fwuser',
                                'kh_fwuser_email',
                                'kh_xsuser',
                                'kh_memo',
                                'kh_is_ykh',
                                'cp_id'));
                
                //$ret = load_model('sys/UserModel_ex')->get_by_id(CTX()->get_session("user_id"));
                $client['kh_updateuser'] = CTX()->get_session("user_id");
                $client['kh_updatedate'] = date('Y-m-d H:i:s');
                $ret = load_model('clients/ClientModel')->update($client, $request['kh_id']);
		exit_json_response($ret);
	}
    //添加岗位信息数据处理。    
    function client_add(array & $request, array & $response, array & $app) {
		$client = get_array_vars($request, 
                        array( 
                                'kh_name',
                                'kh_place',
                               'star_code',
                                'star_password',
                                'kh_address',
                                'kh_tel',
                                'kh_email',
                                'kh_web',
                                'kh_itphone',
                                'kh_itname',
                                'kh_placeuser',
                                'kh_fwuser',
                                'kh_fwuser_email',
                                'kh_xsuser',
                                'kh_memo',
                                'kh_is_ykh',
                                'cp_id'));
                //$ret = load_model('sys/UserModel_ex')->get_by_id(CTX()->get_session("user_id"));
                $client['kh_code'] = uniqid();
                $client['kh_createuser'] = CTX()->get_session("user_id");
                $client['kh_createdate'] = date('Y-m-d H:i:s');
                $client['kh_verify_status'] = '1';
		$ret = load_model('clients/ClientModel')->insert($client);
                
                
                
		exit_json_response($ret);
	}
    
    
    //查询店铺信息
    function shoplist(array & $request, array & $response, array & $app){
        $app['tpl']='clients/clientdetail/clientinfo_shoplist';
    }
    
    //客户云主机查询
    function vmlist(array & $request, array & $response, array & $app){
        $app['tpl']='clients/clientdetail/clientinfo_vmlist';
    }
    
    //客户RDS查询
    function rdslist(array & $request, array & $response, array & $app){
        $app['tpl']='clients/clientdetail/clientinfo_rdslist';
    }
    //客户订购查询
    function orderlist(array & $request, array & $response, array & $app){
        $app['tpl']='clients/clientdetail/clientinfo_orderlist';
    }
    
    //客户授权查询
    function authlist(array & $request, array & $response, array & $app){
        $app['tpl']='clients/clientdetail/clientinfo_authlist';
    }
    
    //客户审核，更新状态
    function  do_check_clients (array & $request, array & $response, array & $app) {
        if (isset($request['_id'])) {
            $client_autoinfo['kh_verify_status'] = "1";
            $client_autoinfo['kh_check_user'] = CTX()->get_session("user_id");
            $client_autoinfo['kh_check_date'] = date('Y-m-d H:i:s');
            $authinfo_status = load_model('clients/ClientModel')->update_check_client($client_autoinfo,$request['_id']);
            exit_json_response($authinfo_status);
        }
    }
    function update_kh_sys_auth(array & $request, array & $response, array & $app) {
        $ret = load_model('clients/ClientModel')->update_check_client($request['kh_id']);
        exit_json_response($ret);
    }

}