<?php
require_lib ( 'util/web_util', true );
require_lib("keylock_util");
class Index {
    
    function do_index(array & $request, array & $response, array & $app) {
        $response['menutype']="1";
    }

    //客户点击注册后提交的数据
    function do_client_register(array & $request, array & $response, array & $app) {
        $client = get_array_vars($request, 
                    array( 
                            'kh_code',
                            'kh_name',
                            'kh_address',
                            'kh_tel',
                            'kh_email',
                            'kh_licence_num',
                            'kh_licence_img',
                            'kh_itphone',
                            'kh_itname',
                            'kh_account_type',
                            ));
            $client['kh_createdate'] = date('Y-m-d H:i:s');
            $client['kh_createuser'] = "2";
            $client['kh_updateuser'] = "2";
            $client['kh_updatedate'] = date('Y-m-d H:i:s');
            $client['kh_verify_status'] = '0';
            $client['kh_login_pwd'] = isset($request['kh_login_pwd']) ? substr(base64_decode($request['kh_login_pwd']), 3, -3) : '';
            $keylock=get_keylock_string(date('Y-m-d'));
            $client['kh_login_pwd'] = create_aes_encrypt($request['kh_login_pwd'],$keylock);
            //默认销售渠道
            $client['kh_place'] = "85";  //默认翼商在线订购
            $ret = load_model('RegisterModel')->insert($client);
            //注册成功
            if($ret['status']=1){
                
            }
            exit_json_response($ret);
    }
        
    //客户点击登录后提交的数据
    function do_user_login(array & $request, array & $response, array & $app) {
         /*$ret = load_model('website/RegisterModel')->get_user_pwd($request['username']);
         $keylock=get_keylock_string($ret['data']['kh_createdate']);
         $ret['data']['kh_login_pwd'] = create_aes_decrypt($ret['data']['kh_login_pwd'],$keylock);

         if($ret['data']['kh_login_pwd'] == $request['userpwd']){
             exit_json_response(load_model('website/RegisterModel')->format_ret("1", '', '登录成功啦'));
         }else{
              exit_json_response(load_model('website/RegisterModel')->format_ret("-1", '', '账号或者密码错误'));
         }*/
        $username = isset($request['username']) ? $request['username'] : '';   //获取用户登录名
        $password = isset($request['userpwd']) ? substr(base64_decode($request['userpwd']), 3, -3) : '';   //获取用户登录密码
        $remember = $request['remb'];                                      //是否记住用户名
        $captcha = $request['captcha'];                                        //验证码
            
        if($remember == 1){//说明要记住用户名
            setcookie('username',$username,time()+3600);
            setcookie('userpwd',$password,time()+3600);
            setcookie('remember',$remember,time()+3600);
        }else{
            setcookie('username',$username,time()-3600);
            setcookie('userpwd',$password,time()-3600);
            setcookie('remember',$remember,time()-3600);
        }
        
        $userinfo = load_model('RegisterModel')->get_user_info($username);
        $keylock=get_keylock_string($userinfo['data']['kh_createdate']);
        $password = create_aes_encrypt($password,$keylock);
        
        //登录操作
        $ret=load_model('RegisterModel')->checklogOn($userinfo,$username,$password,$captcha);
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
    
    //退出登录
    function do_logout(array & $request, array & $response, array & $app){
        $userid=CTX()->get_session("LoginState");
        session_unset();//释放$_SESSION变量
        session_destroy();//删除session文件,释放session_id
        CTX()->redirect('index/do_index');
    }
    
    //注册成功调转提示页面
    function register_suc(array & $request, array & $response, array & $app) {
        $app['tpl'] = 'register_suc';  //显式指定view页面
    }
    
    /**
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
    
    //上传扫描件操作
    function uploadlicenceimg(array & $request,array & $response,array & $app){
        $licenceimg=CTX()->get_app_conf('licenceimg');
        $arrType=$licenceimg['arrType'];
        $max_size=$licenceimg['max_size'];      // 最大文件限制（单位：byte）
        $upfile=$licenceimg['upfile'];  //图片目录路径
        $file=$_FILES['upfile'];
        if(!is_uploaded_file($file['tmp_name'])){ //判断上传文件是否存在
            //文件不存在
            exit_json_response(load_model('RegisterModel')->format_ret("-1", '', '上传失败'));
        }
        if($file['size']>$max_size){  //判断文件大小是否大于5242880字节
            //上传文件太大;
            exit_json_response(load_model('RegisterModel')->format_ret("-1", '', '上传文件超出大小'));
        }
        if(!in_array($file['type'],$arrType)){  //判断图片文件的格式
            //上传文件格式不对;
            exit_json_response(load_model('RegisterModel')->format_ret("-1", '', '上传文件格式不对'));
        }
        if(!file_exists($upfile)){  // 判断存放文件目录是否存在
            mkdir($upfile,0777,true);
        } 
        $imageSize=getimagesize($file['tmp_name']);
        $img=$imageSize[0].'*'.$imageSize[1];
        $fname=$file['name'];
        $ftype=explode('.',$fname);
        $picName=$upfile."/".uniqid().".".$ftype[1];
        if(file_exists($picName)){
            //>同文件名已存在;
            exit_json_response(load_model('RegisterModel')->format_ret("-1", '', '上传失败'));
        }
        if(!move_uploaded_file($file['tmp_name'],$picName)){ 
            //移动文件出错;
            exit_json_response(load_model('RegisterModel')->format_ret("-1", '', '上传失败'));
        }
        else{
            exit_json_response(load_model('RegisterModel')->format_ret("1",array('imgpath'=>$picName), 'success'));
            //上传成功，返回
            //echo "<font color='#FF0000'>图片文件上传成功！</font><br/>";
            //echo "<font color='#0000FF'>图片大小：$img</font><br/>";
            //echo "图片预览：<br><div style='border:#F00 1px solid; width:200px;height:200px'>
            //<img src=\"".$picName."\" width=200px height=200px>".$fname."</div>";
        }
    }
    
    //查看服务协议
    function show_serarg(){
        
    }
    
    function check_kh_name(array & $request,array & $response,array & $app){
        $ret = load_model('mycenter/MyselfModel')->check_khinfo('kh_name',$request['kh_name']);
        if($ret>0){
            exit_json_response(-1);
        }else{
            exit_json_response(1);
        }
    }
    
    function check_kh_code(array & $request,array & $response,array & $app){
        $ret = load_model('mycenter/MyselfModel')->check_khinfo('kh_code',$request['kh_code']);
        if($ret>0){
            exit_json_response(-1);
        }else{
            exit_json_response(1);
        }
    }
    
    function check_licence_num(array & $request,array & $response,array & $app){
        $ret = load_model('mycenter/MyselfModel')->check_khinfo('kh_licence_num',$request['licence_num']);
        if($ret>0){
            exit_json_response(-1);
        }else{
            exit_json_response(1);
        }
    }
}