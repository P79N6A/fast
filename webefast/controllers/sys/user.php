<?php
require_lib ( 'util/web_util', true );
class User {
	function do_list(array & $request, array & $response, array & $app) {

	}
	function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑用户', 'add'=>'添加用户', 'view'=>'查看用户');
		$app['title'] = $title_arr[$app['scene']];
		if ($app['scene'] == 'edit' || $app['scene'] == 'view') {
		$ret = load_model('sys/UserModel')->query_by_id($request['_id']);
		if ($ret['status'] > 0) {
			$response['data'] = $ret['data'];
		}
		}
	}
	
	function do_edit(array & $request, array & $response, array & $app) {
		$user = get_array_vars($request, array('user_name', 'sex', 'email','phone'));
		$ret = load_model('sys/UserModel')->update($user, $request['user_id']);
		exit_json_response($ret);
	}
	
	function do_add(array & $request, array & $response, array & $app) {
		$user = get_array_vars($request, array('user_code', 'user_name', 'sex', 'email','phone'));
		$ret = load_model('sys/UserModel')->insert($user);
		exit_json_response($ret);
	}
	
	function reset_pwd(array & $request, array & $response, array & $app) {
            
                $ret = load_model('sys/SysAuthModel')->check_is_auth();
                if($ret['status']<0){
                   exit_json_response($ret); 
                }
            
		$ret = load_model('sys/UserModel')->reset_pwd($request['user_id']);
                
                $ret['data'] = load_model('sys/UserModel')->get_passwod( $ret['data']);
                
		exit_json_response($ret);
	}
	
	function update_active(array & $request, array & $response, array & $app) {
		$arr = array('enable'=>1, 'disable'=>0);
		$ret = load_model('sys/UserModel')->update_active($arr[$request['type']], $request['user_id']);
		exit_json_response($ret);
	}

        /**
         * 删除无效用户 */
        function do_delete(array & $request, array & $response, array & $app){
            $ret = load_model('sys/UserModel')->delete($request['user_id']);
            exit_json_response($ret);
        }
        
	function role_list(array & $request, array & $response, array & $app) {
		
	}
	
	function role_add(array & $request, array & $response, array & $app) {
	    $role_ids = explode(',', $request['role_ids']);
	    $ret = load_model('sys/UserModel')->add_role($request['user_id'], $role_ids);
	    exit_json_response($ret);
	}
	
	function role_delete(array & $request, array & $response, array & $app) {
	    $role_ids = explode(',', $request['role_ids']);
	    $ret = load_model('sys/UserModel')->delete_role($request['user_id'], $role_ids);
	    exit_json_response($ret);
	}
	
    function loginlog(array & $request, array & $response, array & $app) {
	   
	} 

	function user_remove_role(array & $request, array & $response, array & $app) {
	    $ret = load_model('sys/UserModel')->user_remove_role($request['user_id'],$request['sel_role_id']);
	    exit_json_response($ret);
	}

	function user_add_role(array & $request, array & $response, array & $app) {
        if(isset($request['user_id']) || !empty($request['user_id'])){
            $user_id = $request['user_id'];
        }else{
            $user_id = CTX()->get_session('user_id');
        }
	    $ret = load_model('sys/UserModel')->user_add_role($user_id, $request['sel_role_id']);
	    exit_json_response($ret);
	}
        
        function detail_user_pref(array & $request, array & $response, array & $app) {
            $ret = load_model('sys/UserModel')->detail_user_pref($request['iid']);
	    exit_json_response($ret);
        }
        
        
        
}