<?php
class user_add {

	function do_add(array & $request, array & $response, array & $app) {
		$user = get_array_vars($request, array('user_code', 'user_name', 'sex', 'email','phone'));
		$pwd = 'yswl2015';
		$user['password'] = md5(md5($pwd).$pwd);
		
		$sql = "select count(*) as sum from sys_user where status = 1";
        $res = ctx()->db->getRow($sql);
		
		$sql = "select value from sys_auth where code = 'auth_num'  ";
        $arr = ctx()->db->getRow($sql);
		 
        if($res['sum'] >= $arr['value'])
        $user['status'] = 0;
        
        $ret = load_model('sys/UserModel')->insert($user);
		exit_json_response($ret);
	}

}