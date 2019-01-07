<?php
require_lib ( 'util/web_util', true );
class Login {
        
    function init_login(array & $request, array & $response, array & $app) {
        $app['tpl'] = 'login_init_login';
        $app['page']= 'null';
    }
    
    function do_login(array & $request, array & $response, array & $app) {
        
        $username = isset($request['username']) ? $request['username'] : '';   //获取用户登录名
        $password = isset($request['password']) ? $request['password'] : '';   //获取用户登录密码
        $remember = $request['remember'];                                      //是否记住用户名
        $captcha = $request['captcha'];                                        //验证码
            
        if($remember == 1){//说明要记住用户名
            setcookie('username',$username,time()+3600);
            setcookie('remember',$remember,time()+3600);
        }
        //登录操作
        $ret=load_model('sys/UserModel_ex')->checklogOn($username,$password,$captcha);
        
         load_model('sys/LoginCheckModel')->set_login_safe($ret,$username);
        
        //记录登录次数
        if($ret['status']=="-1"){
            if(CTX()->get_session("loginsum")!=""){
                CTX()->set_session("loginsum",CTX()->get_session("loginsum")+1);
            }
            else{
                CTX()->set_session("loginsum",1);
            }
        }else
        {
            CTX()->set_session("loginsum","0");
        }
        exit_json_response($ret);
    }
    
    function do_logout(array & $request, array & $response, array & $app){
        $userid=CTX()->get_session("user_id");
        session_unset();//释放$_SESSION变量
        session_destroy();//删除session文件,释放session_id
        CTX()->redirect('login/init_login');
    }/**
     * 验证码
     */
    function captcha(array & $request, array & $response, array & $app) {
        include ROOT_PATH .  CTX()->app_name . '/plugins/captcha/captcha.php';
        $code = (isset($request['code'])) ? $request['code'] : '';
        if ($code == 'code') {
            $gif = new GIF();
            echo $gif->init(90, 47, '26, 189, 157'); //参数依次是长、宽以及图片背景色
            die();
        }
    }
    
    function login_safe_check(array & $request, array & $response, array & $app){
          CTX()->set_session("IsLogin",TRUE); // 
          CTX()->redirect('index/do_index');

    }

}