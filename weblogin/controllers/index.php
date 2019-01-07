<?php
require_lib ( 'util/web_util', true );
require_lib ( 'password_util', true );
class Index {
	function do_index(array & $request, array & $response, array & $app) {
        echo "<script>location.href='?app_act=index/login';</script>";
        die;
    }
      function login_app(array & $request, array & $response, array & $app) {
          $this->login($request, $response, $app);
          $app['tpl'] = 'login_app';
                      
      }
      function do_login_app(array & $request, array & $response, array & $app) {
		
			
            $response = load_model('sys/LoginModel')->login_by_form($request);
			CTX()->redirect($response['data']);
                      
      }
	function login(array & $request, array & $response, array & $app) {

        //判断是否系统维护中
        $result = load_model('sys/LoginModel')->get_maintain();
        
          $app['page']= 'NULL';  

        if($result=="1"){//表示系统维护中 
            $app['page']= 'null';   //取消引入默认页
            $app['tpl'] = 'maintain';
            //获取运营平台实时公告信息
            $data= load_model('sys/LoginModel')->get_maintain_info();
            $response['data']=$data;
            return;
        }
         //设置cookies
        $cookie_time = time()+60*60*24*365;
        if(!empty($request['user_code'])){
          $request['user_code'] = trim($request['user_code']);
           $check = $this->check_str($request['user_code']);
            if($check!==true){
                  $app['fmt'] = 'json';
                return $response=$check;
            }
            setcookie("remember_user_code", $request['user_code'],$cookie_time,null,null,null,true);
        }
        if(!empty($request['customer_name'])){
            $request['customer_name'] = trim($request['customer_name']);
            $check = $this->check_str($request['customer_name']);
            if($check!==true){
                  $app['fmt'] = 'json';
                      return $response=$check;
            }
            setcookie("remember_customer_name", $request['customer_name'],$cookie_time,null,null,null,true);
        }
		if (@$request['do'] == 1) {       
                        $password = substr(get_passwod($request['password']), 3);
         
			$request['password'] = get_passwod($password);
            $response = load_model('sys/LoginModel')->login_by_form($request);
            
                    //添加登录日志

            $service_security = '';//后端人员手机号替换
            if (isset($request['service_security']) && !empty($request['service_security'])) {
                $service_security = $request['service_security'];
            }
                    $ret =  load_model("sys/LoginCheckModel")->set_login_safe($response,$service_security);
                  
                     
            
				$app['fmt'] = 'json';
			} else {
				header("Content-type: text/html; charset=utf-8");
	            $response['show_captcha'] = CTX()->get_session('show_captcha');

	            //echo '<hr/>$xx<xmp>'.var_export($response['show_captcha'],true).'</xmp>';die;
				//$app['tpl'] = 'login';
                                    $app['tpl'] = 'login_new';
                  
                                
			}
     
	}
	function login_new(array & $request, array & $response, array & $app) {

        //判断是否系统维护中

          $app['page']= 'NULL';  

     
         //设置cookies
        $cookie_time = time()+60*60*24*365;
        if(!empty($request['user_code'])){
          $request['user_code'] = trim($request['user_code']);
           $check = $this->check_str($request['user_code']);
            if($check!==true){
                  $app['fmt'] = 'json';
                return $response=$check;
            }
            setcookie("remember_user_code", $request['user_code'],$cookie_time,null,null,null,true);
        }
        if(!empty($request['customer_name'])){
            $request['customer_name'] = trim($request['customer_name']);
            $check = $this->check_str($request['customer_name']);
            if($check!==true){
                  $app['fmt'] = 'json';
                      return $response=$check;
            }
            setcookie("remember_customer_name", $request['customer_name'],$cookie_time,null,null,null,true);
        }
		if (@$request['do'] == 1) {       
                        $password = substr(get_passwod($request['password']), 3);
         
			$request['password'] = get_passwod($password);
            $response = load_model('sys/LoginModel')->login_by_form($request);
            
                    //添加登录日志
                    $ret =  load_model("sys/LoginCheckModel")->set_login_safe($response);
                  
                     
            
				$app['fmt'] = 'json';
			} else {
				header("Content-type: text/html; charset=utf-8");
	            $response['show_captcha'] = CTX()->get_session('show_captcha');

	            //echo '<hr/>$xx<xmp>'.var_export($response['show_captcha'],true).'</xmp>';die;
				//$app['tpl'] = 'login';
                                  $app['tpl'] = 'login_new_other';
                  
                                
			}
     
	}
    //从平台点 使用 过来的入口
	function login_by_platform(array & $request, array & $response, array & $app) {
        $ret = load_model('sys/LoginModel')->login_by_platform($request);
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';die;
        if($ret['status']== -1){
            echo $ret['message'];
            die;
        }
        if($ret['status']== -2){
            //是否要录入授权码
            echo "<script>location.href = '{$ret['data']}';</script>";
            die;
        }
        if($ret['status']== 1){
            //已订购过
            echo "<script>location.href = '{$ret['data']}';</script>";
            die;
        }
	}

    //询问是否新客户
	function ask_is_new_customer(array & $request, array & $response, array & $app) {
        die;
    }

    //未订购过的新客户
	function create_new_customer(array & $request, array & $response, array & $app) {
        die;
        $response = load_model('sys/LoginModel')->create_new_customer($request);
        echo json_encode($response);
        die;
    }

    //用授权码登录
	function login_by_auth_key(array & $request, array & $response, array & $app) {
        $response = load_model('sys/LoginModel')->login_by_auth_key($request);
        echo json_encode($response);
        die;
    }

    //efast授权入口(从服务平台点进来，没有授权码)
	function efast_auth_shop(array & $request, array & $response, array & $app) {

    }

    //直接从EFAST店铺列表点进来，有自动带授权码的
    function from_efast_auth_shop(array & $request, array & $response, array & $app) {
        $response = load_model('sys/LoginModel')->from_efast_auth_shop($request);
        if($response['status']<0){
            echo $response['message'];
        }else{
            $url = $response['data'];
            echo "<script>location.href = '{$url}';</script>";
        }
        die;
    }

    //从服务平台过来的,可能是新客户，也可能是老客户
    function from_fuwu_taobao(array & $request, array & $response, array & $app) {
        $response = load_model('sys/LoginModel')->from_fuwu_taobao($request);
        if($response['status'] == -1){
            echo $response['message'];
            die;
        }
        if($response['status'] == -2){
            $url = $response['data'];
            if($url == ''){
              echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';
            }else{
                echo "<script>location.href = '{$url}';</script>";
            }
            die;
        }
        if($response['status'] == 1){
            $url = $response['data'];
            echo "<script>location.href = '{$url}';</script>";
            die;
        }
    }
       function from_fuwu_alibaba(array & $request, array & $response, array & $app) {
             $response = load_model('sys/LoginModel')->from_fuwu_alibaba($request);
        if($response['status'] == -1){
            echo $response['message'];
            die;
        }
        if($response['status'] == -2){
            $url = $response['data'];
            if($url == ''){
              echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';
            }else{
                echo "<script>location.href = '{$url}';</script>";
            }
            die;
        }
        if($response['status'] == 1){
            $url = $response['data'];
            echo "<script>location.href = '{$url}';</script>";
            die;
        } 
    } 
    /*
    * 京东授权—服务平台授权操作
    * @author WangShouChong
    */
    function from_fuwu_jingdong(array & $request, array & $response, array & $app) {
        $response = load_model('sys/LoginModel')->from_fuwu_jingdong($request);
        if($response['status'] == -1){
            echo $response['message'];
            die;
        }
        if($response['status'] == -2){
            $url = $response['data'];
            if($url == ''){
                echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';
            }else{
                echo "<script>location.href = '{$url}';</script>";
            }
            die;
        }
        if($response['status'] == 1){
            $url = $response['data'];
            echo "<script>location.href = '{$url}';</script>";
            die;
        }
    }
    
    /*
    * 一号店授权—服务平台授权操作
    * @author WangShouChong
    */
    function from_fuwu_yihaodian(array & $request, array & $response, array & $app) {
        $response = load_model('sys/LoginModel')->from_fuwu_yihaodian($request);
        if($response['status'] == -1){
            echo $response['message'];
            die;
        }
        if($response['status'] == -2){
            $url = $response['data'];
            if($url == ''){
                echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';
            }else{
                echo "<script>location.href = '{$url}';</script>";
            }
            die;
        }
        if($response['status'] == 1){
            $url = $response['data'];
            echo "<script>location.href = '{$url}';</script>";
            die;
        }
    }
    

    //输入授权码
    function input_auth_key(array & $request, array & $response, array & $app) {

    }

    //处理输入的授权码
    function act_input_auth_key(array & $request, array & $response, array & $app) {
        $response = load_model('sys/LoginModel')->act_input_auth_key($request);
        echo json_encode($response);
        die;
    }

    //efast授权保存(直接从EFAST店铺列表点进来，有自动带授权码的，直接进入这里)
	function save_auth_shop(array & $request, array & $response, array & $app) {
        $response = load_model('sys/LoginModel')->save_auth_shop($request);
        if($response['status']<0){
            echo $response['message'];
        }else{
            $url = $response['data'];
            echo "<script>location.href = '{$url}';</script>";
        }
        die;
    }

    function set_init_sys_end(array & $request, array & $response, array & $app) {
        $response = load_model('sys/LoginModel')->set_init_sys_end($request);
        echo json_encode($response);
        die;
    }

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
        
         load_model("sys/LoginCheckModel")->is_verify_passed($request['token'],$request['appkey']);
                     
    }
    
    function check_customer_name(array & $request, array & $response, array & $app) {
        $check = $this->check_str($request['user_code']);
        if ($check !== true) {
            //$app['fmt'] = 'json';
            return $response = $check;
        }
        $response = load_model("sys/LoginModel")->get_app_key_by_customer_name($request['customer_name']);
    }

    function check_str($str) {
        // if (preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/", $str)) {  //不允许特殊字符
        if (preg_match("/[\',:;*?~`!#$%^&+=<>{}]|\]|\[|\/|\\\|\"|\|/", $str)) {  //不允许特殊字符
            return array('status' => -1,'data'=>'', 'message' => '异常登录！');
        }
        return true;
    }
}