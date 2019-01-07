<?php
require_lib ( 'util/web_util', true );
class User {
	function do_list(array & $request, array & $response, array & $app) {
		
	}
	function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑用户', 'add'=>'添加用户', 'view'=>'查看用户');
		$app['title'] = $title_arr[$app['scene']];
                if(isset($request['_id'])){
                    $ret = load_model('sys/UserModel_ex')->get_by_id($request['_id']);
                }else{
                    $ret = load_model('sys/UserModel_ex')->get_by_id(CTX()->get_session("user_id"));
                }
		$response['data'] = $ret['data'];
	}
	
	function do_edit(array & $request, array & $response, array & $app) {
                //初始化数组
                $data=array('user_code','user_name','user_sex','user_phone','user_mobile','user_work_no','user_birthday','user_worked'
                    ,'user_education','user_title','user_post','user_work_limit','user_ident_no','user_in_date'
                    ,'user_address','user_postal','user_remark','user_admin');
		$user = get_array_vars($request, $data);
                $sys_data=array('user_code'=>$request['user_code'],'user_name'=>$request['user_name'],'sex'=>$request['user_sex'],'is_admin'=>$request['user_admin']);
		$ret = load_model('sys/UserModel_ex')->update($user, $request['user_id'],$sys_data);
		exit_json_response($ret);
	}
	
	function do_add(array & $request, array & $response, array & $app) {
		$user = get_array_vars($request, array('user_code', 'user_name', 'sex', 'email'));
		$ret = load_model('sys/UserModel_ex')->insert($user);
		exit_json_response($ret);
	}


        function role_list(array & $request, array & $response, array & $app) {
		
	}
	
	function role_add(array & $request, array & $response, array & $app) {
	    $role_ids = explode(',', $request['role_ids']);
	    $ret = load_model('sys/UserModel_ex')->add_role($request['user_id'], $role_ids);
	    exit_json_response($ret);
	}
	
	function role_delete(array & $request, array & $response, array & $app) {
	    $role_ids = explode(',', $request['role_ids']);
	    $ret = load_model('sys/UserModel_ex')->delete_role($request['user_id'], $role_ids);
	    exit_json_response($ret);
	}
        
        
        function user_remove_role(array & $request, array & $response, array & $app) {
	    //$ret = load_model('sys/UserModel')->user_remove_role(CTX()->get_session('user_id'),$request['sel_role_id']);
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
        
        
        //获取当前登录用户个人信息
        function detail_info(array & $request, array & $response, array & $app){
//            $app["tpl"]="sys/user_detail_info";
//            $title_arr = array('edit'=>'编辑用户');
//            $app['title'] = $title_arr[$app['scene']];
//            $ret = load_model('sys/UserModel_ex')->get_by_id(CTX()->get_session("user_id"));
//            $response['data'] = $ret['data'];
            
            $app["tpl"]="sys/user_detail";
            $title_arr = array('edit'=>'编辑用户', 'add'=>'添加用户', 'view'=>'查看用户');
            $app['title'] = $title_arr[$app['scene']];
            if(isset($request['_id'])){
                $ret = load_model('sys/UserModel_ex')->get_by_id($request['_id']);
            }else{
                $ret = load_model('sys/UserModel_ex')->get_by_id(CTX()->get_session("user_id"));
            }
            $response['data'] = $ret['data'];
        }
        
        //重置密码
        function reset_pwd(array & $request, array & $response, array & $app){
            $ret = load_model('sys/UserModel_ex')->reset_pwd($request['user_id']);
            exit_json_response($ret);
        }
        
        //修改用户密码
        function do_chgpasswd(array & $request, array & $response, array & $app){
            
            //dnew_user_pwd
            if(isset($request["do"])){
                //标识post请求
                $this->check_pwd($request);
                $oldpwd=$request["old_user_pwd"]; //获取原密码
                $oldpwd=load_model('sys/UserModel_ex')->getMd5_ToBase64($oldpwd);  //加密操作
                $ret=load_model('sys/UserModel_ex')->getuser_pwd(CTX()->get_session("user_id"));
                if($ret!=""){
                    if($oldpwd!=$ret){//原密码验证不通过
                        exit_json_response(load_model('sys/UserModel_ex')->format_ret("-1", '', '原密码错误'));
                    }
                    else{
                        //更新密码
                        $newpwd=$request["new_user_pwd"];  //获取新密码
                        $newpwd=load_model('sys/UserModel_ex')->getMd5_ToBase64($newpwd);  //加密操作
                        $result=load_model('sys/UserModel_ex')->updatepwd(CTX()->get_session("user_id"),$newpwd);
                        exit_json_response($result);
                    }
                }else{
                    //原密码验证不通过
                    exit_json_response(load_model('sys/UserModel_ex')->format_ret("-1", '', '原密码错误'));
                }
            }
        }
            function check_pwd($request){
            $current_pwd= $newpwd='';
            //old_user_pwd
            //dnew_user_pwd  new_user_pwd
            if(isset($request['old_user_pwd'])&&$request['old_user_pwd']!=''){
                    $current_pwd = $request['old_user_pwd'];
            }else{
                    exit_json_response(-1,array(),"当前密码不能为空");
            }
            
            if(isset($request['new_user_pwd'])&&$request['new_user_pwd']!=''){
                    $new_pwd = $request['new_user_pwd'];
            }else{
                    exit_json_response(-1,array(),"新密码不能为空");
            }
            
            if(isset($request['dnew_user_pwd'])&&$request['dnew_user_pwd']!=''){
                    $sure_pwd = $request['dnew_user_pwd'];
            }else{
                    exit_json_response(-1,array(),"确认密码不能为空");
            }
            
            
//            if(strlen($new_pwd)<6  ||  strlen($new_pwd)>12){
//                    exit_json_response(-1,array(),"密码长度必须为6-12位");
//            }
    		if(strlen($new_pwd)<8  ||  strlen($new_pwd)>20){
                    exit_json_response(-1,array(),"密码长度必须为8-20位");
            }
            if($new_pwd!==$sure_pwd){
                    exit_json_response(-1,array(),"确认密码与新密码不一致");
            }
            if($new_pwd==$current_pwd){
                    exit_json_response(-1,array(),"新密码不能与当前密码相同");
            }
//            if((preg_match("/[a-zA-Z]+/", $new_pwd)*preg_match("/[0-9]+/", $new_pwd))==0||preg_match("/[^0-9a-zA-Z]+/", $new_pwd)){
//                    exit_json_response(-1,array(),"密码只能为字母和数字的组合");
//            }
            
            $is_strong_safe = CTX()->get_app_conf('is_strong_safe');
         
         
            if($is_strong_safe){
            if(preg_match("/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/", $new_pwd) == false){
                    exit_json_response(-1,array(),"密码须为数字、大写字母、小写字母和特殊符号的组合");
            }}
          
            
            
            
            

    }
        
        
        
}