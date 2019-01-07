<?php

require_lib ( 'util/web_util', true );
class Index {
	function do_index(array & $request, array & $response, array & $app) {

                   $is_strong = CTX()->get_session('no_strong');
                   if(!empty($is_strong)){
                               CTX()->redirect('index/change_password&reason=psw_strong');
                            exit;
                   }
        //在线客服，保证买断版（独享型）新客户（客户档期新增时间>2017-03-01）不能使用
        $kh_id = CTX()->saas->get_saas_key();
        //$pra_strategytype = load_model('sys/EfastUserModel')->get_strategytype($kh_id);
        $response['pra_strategytype'] = $pra_strategytype['status'] == 1 ? $pra_strategytype['data'] : 2;
        $model = load_model('sys/EfastPrivilegeModel');
		$response['top_menu'] = $model->get_top_menu(true);
		$response['menu_tree'] = $model->get_menu_tree(true);
		$response['user_name'] = CTX()->get_session('user_name');
                $response['user_code'] = CTX()->get_session('user_code');
                $response['md5kh_id'] = md5(CTX()->get_session('kh_id'));
                $apiurl=require_conf("api_url");
                $response['operateurl'] = $apiurl['operate'];
		$arr = array('psw_strong');
		$ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
		$response['psw_strong'] =isset($ret_arr['psw_strong'])?$ret_arr['psw_strong']:'' ;
                $is_strong_safe = CTX()->get_app_conf('is_strong_safe');
                if($is_strong_safe){
                    $response['psw_strong'] = 1;
                }
        $response['echarts_index'] = $this->get_echarts_index();
		$app['page'] = 'null';
        $app['tpl'] = 'index_do_index_new';
     	$response['home_page'] = array(
            'order'=>array(
                    'id'=>'index/order',
                )
            );
            if(!load_model('sys/PrivilegeModel')->check_priv('index/order')){
                $response['home_page'] = array();
            }


            $ret = load_model('sys/SysAuthModel')->get_cp_area();
            $response['cp_area']  = strtolower($ret['data']);


           //  $logo_arr = array('efast5_standard','efast5_ultimate','efast5_enterprise');//efast5_Standard  efast5_Ultimate
             //efast5_Standard,efast5_Ultimate,efast5_Enterprise

//
//                 'json'=>",{
//                        text:'订单处理流程',
//                        items:[{id:'index/order', text:'订单处理流程', href:'?app_act=index/order', closeable : false}]
//                    }"
//
	}
        function get_echarts_index(){
            $response['user_code'] = CTX()->get_session('user_code');
            $sql = "select echarts_index from sys_user where user_code = :user_code";
            $echarts_index = ctx()->db->getOne($sql,array(':user_code' => $response['user_code']));
            $login_type = CTX()->get_session('login_type');
            if($echarts_index ==1 || $login_type == 2){
                $echarts_index ='sys/echarts/view_index_data';
            }else{
                $echarts_index ='sys/echarts/view_index';
            }
            return $echarts_index;
        }
        function get_menu(array & $request, array & $response, array & $app) {
                $model = load_model('sys/EfastPrivilegeModel');
            $home_page = array(
            'order'=>array(
                    'id'=>'index/order',
                )
            );
            $menu_tree = $model->get_menu_tree(true,$request['code']);
            $new_menu_tree = array();
           foreach($menu_tree as $cote){
               $new_row = array();
               $new_row['id'] = $cote['action_code'];
              if(isset($home_page[$cote['action_code']])){
                  $new_row['homePage'] = $home_page[$cote['action_code']]['id'];
              }
              $new_row['menu'] = array();
              foreach ($cote['_child'] as $group){
                     $new_group['text'] =  $group['action_name'];
                     $new_group['items'] =  array();
                     foreach ($group['_child'] as $url){
                         $new_url = array();
                         $new_url['id'] = $url['action_code'];
                         $new_url['text'] = $url['action_name'];
                         $new_url['href'] = get_app_url($url['action_code'])."&ES_frmId=".$url['action_code'];
                         if(isset($home_page[$cote['action_code']])&&$home_page[$cote['action_code']]['id']===$url['action_code']){
                            $new_url['closeable'] = false;
                         }
                         $new_group['items'][] = $new_url;

                     }
                      $new_row['menu'][]=$new_group;
              }
              $new_menu_tree[] = $new_row;
           }
           $response['data'] = $new_menu_tree;
        }
	function welcome(array & $request, array & $response, array & $app) {

	}
        function order(array & $request, array & $response, array & $app) {
		$app['page'] = NULL;
	}

    function api(array & $request, array & $response, array & $app) {
        set_ui_entry(UI_ENTRY_API);

        $model = load_model('sys/EfastPrivilegeModel');
        $response['top_menu'] = $model->get_top_menu(true);
        $response['menu_tree'] = $model->get_menu_tree(true);
        $response['user_name'] = CTX()->get_session('user_name');
        	$app['page'] = 'null';
            $ret = load_model('sys/SysAuthModel')->get_cp_area();
            $response['cp_area']  = strtolower($ret['data']);
        // TODO 临时应用原来的主页面，请完善
        $response['echarts_index'] = $this->get_echarts_index();
        $app['tpl'] = 'index_do_index_new';
    }
     function server_value(array & $request, array & $response, array & $app) {
        set_ui_entry(UI_ENTRY_SERVER);
        $response['list_type'] = 'server_value';

        if(!load_model('sys/PrivilegeModel')->check_priv('server')){
                $role_data = CTX()->get_session('role');
		CTX()->set_session('action_list', null);
                load_model('sys/PrivilegeModel')-> add_entrance_role(  $role_data['data'][0]['role_id']);
        }
        
		$response['user_name'] = CTX()->get_session('user_name');
                $response['user_code'] = CTX()->get_session('user_code');
                $response['md5kh_id'] = md5(CTX()->get_session('kh_id'));
                $apiurl=require_conf("api_url");
                $response['operateurl'] = $apiurl['operate'];
        $model = load_model('sys/EfastPrivilegeModel');
        $response['top_menu'] = $model->get_top_menu(true);
        $response['menu_tree'] = $model->get_menu_tree(true);
        $response['user_name'] = CTX()->get_session('user_name');
        	$app['page'] = 'null';
            $ret = load_model('sys/SysAuthModel')->get_cp_area();
            $response['cp_area']  = strtolower($ret['data']);
        // TODO 临时应用原来的主页面，请完善
         $response['echarts_index'] = $this->get_echarts_index();
        $app['tpl'] = 'index_do_index_new';
        //value/value_add/server_list
    }
    
    
    
    

    function increment(array & $request, array & $response, array & $app) {
        set_ui_entry(UI_ENTRY_INCREMENT);

        $model = load_model('sys/EfastPrivilegeModel');
        $response['top_menu'] = $model->get_top_menu(true);
        $response['menu_tree'] = $model->get_menu_tree(true);
        $response['user_name'] = CTX()->get_session('user_name');
        $app['page'] = 'null';
        $ret = load_model('sys/SysAuthModel')->get_cp_area();
        $response['cp_area'] = strtolower($ret['data']);
        // TODO 临时应用原来的主页面，请完善
        $response['echarts_index'] = $this->get_echarts_index();
        $app['tpl'] = 'index_do_index_new';

        $response['home_page'] = array(
            'issue' => array(
                'id' => '',
            )
        );
    }

    function message(array & $request, array & $response, array & $app) {
        $app['page'] = 'null';
        $response['login_type'] = CTX()->get_session('login_type');
        $response['shortcut_menu'] = load_model('sys/ShortcutMenuModel')->get_by_user_id();
        if ($request['page_type'] == 'increment') {
            die;
        }

//        require_model('oms/SellRecordModel');
//        $m = new SellRecordModel();
//        $response['all'] = $m->count_by('all');
//        $response['pay'] = $m->count_by('pay');
//        $response['confirm'] = $m->count_by('confirm');
//        $response['print'] = $m->count_by('print');
//        $response['send'] = $m->count_by('send');
//        $response['back'] = $m->count_by('back');
    }

    function login(array & $request, array & $response, array & $app) {

                if(RUN_MODE=='PRO'){
                    $val = CTX()->get_app_conf('login_server');
                    echo "<script>location.href='".$val."';</script>";
                    die;
                }

		if (isset($request['do']) && $request['do'] == 1) {
			$user = get_array_vars($request, array('user_code', 'password', 'remember'));
			$response = load_model('sys/EfastUserModel')->login($user,0,12);

			$sql = "select lastchanged,is_strong,status from sys_user where user_code = '".$request['user_code']."'";
			$val = ctx()->db->getRow($sql);

			$arr = array('psw_strong','psw_period');
			$ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
			$psw_strong =isset($ret_arr['psw_strong'])?$ret_arr['psw_strong']:'' ;
			$psw_period =isset($ret_arr['psw_period'])?$ret_arr['psw_period']:'' ;

		 	$current = date("Y-m-d H:i:s",time());
			$month=floor((strtotime($current)-strtotime($val['lastchanged']))/86400/30);


			if($psw_strong == 1 && $val['is_strong'] == 2)
			$response['status'] = -5;

			if($val['is_strong'] == 0)
			$response['status'] = -6;

			if($month >= $psw_period)
			$response['status'] = -7;


			if($val['status'] == 0)
			$response['status'] = -9;

                        if($response['status']<0){
                            $response['data'] = array();
                        }


			$app['fmt'] = 'json';
		} else {
                        $app['page'] = 'NULL';
			$app['tpl'] = 'login_new';
		}

               // var_dump($_SESSION);
	}
        function login_app(array & $request, array & $response, array & $app){


                        $this->login($request,$response,$app);
			$app['tpl'] = 'login_app';

        }


	function change_password(array & $request, array & $response, array & $app) {
			$response['user_name'] = CTX()->get_session('user_name');
			$response['reason'] = $_GET['reason'];
			$arr = array('psw_strong');
			$ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
			$response['psw_strong'] =isset($ret_arr['psw_strong'])?$ret_arr['psw_strong']:'' ;
                        $is_strong_safe = CTX()->get_app_conf('is_strong_safe');
                        if($is_strong_safe){
                            $response['psw_strong'] = 1;
                        }
			$app['tpl'] = 'change_password';
                         CTX()->set_session('no_strong',1);
	}
	function logout(array & $request, array & $response, array & $app) {
		load_model('sys/UserModel')->logout();
		CTX()->redirect('index/do_index');
	}

    /**
     * 从云中心跳转过来
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function cloud_login(array & $request, array & $response, array & $app){
        $json = false;
        if(isset($request['fmt'])||'json'==$request['fmt']){
            $app['fmt'] = 'json';
            $json = true;
        }
        //TODO 此处需要优化成，从淘宝登录失败，返回淘宝服务平台的应用地址。从SAAS中心登录入口登录失败，返回SAAS中心的公共登录页面。

        //$cloud_url = $GLOBALS['context']->get_app_conf('cloud_url');

	    //正式上线需要改server_code
        $cloud_url = $GLOBALS['context']->get_app_conf('cloud_url');

        //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        if(!isset($request['token'])||''==$request['token']){
            //token不存在
            if($json){
                exit_json_response(-1, array(), '登录参数TOKEN不存在!');
            }else{
                header("Location:$cloud_url");
            }
            exit;
        }
        //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $login = fast_decode($request['token']);
        if(FALSE==$login){
            if($json){
                exit_json_response(-1, array(), '登录参数TOKEN错误或已过期!');
            }else{
                header("Location:$cloud_url");
            }
            exit;
        }
        //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        //切换数据库链接
        CTX()->db->set_conf(array(
            'name' => $login['kh_code'],
            'host' => CTX()->get_app_conf('db_host'),
            'user' => CTX()->get_app_conf('db_user'),
            'pwd' => CTX()->get_app_conf('db_pass'),
            'type' => 'mysql'
        ));
        //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $response = load_model('sys/UserModel')->login_by_cloud($login['kh_code'],$login['user_code']);
        if($response['status']==1){
            CTX()->redirect('index/do_index');
            exit;
        }else{
            if($json){
                header("Location:$cloud_url");
            }else{
                exit_json_response(-1, array(), '登录失败!'.$response['message']);
            }
            exit;
        }

    }

    /*
     * 修改密码
     * 2014/12/5
     * jia.ceng
     */
    function change_pwd(array & $request, array & $response, array & $app){
              $ret = load_model('sys/SysAuthModel')->check_is_auth();
            if($ret['status']<0){
                   exit_json_response($ret);
             }
               $request['current_pwd'] =  $this->get_new_pwd($request['current_pwd']);
               $request['new_pwd'] =  $this->get_new_pwd($request['new_pwd']);
               $request['sure_pwd'] = $this->get_new_pwd($request['sure_pwd']);
            //   var_dump( $request);exit;
            $this->check_pwd($request);
            $user_mdl = load_model('sys/UserModel');
            $user = $user_mdl->get_row(array("user_id"=>$_SESSION['user_id']));
            $current_pwd = $user_mdl->encode_pwd($request['current_pwd']);

            if($user['status']==1&&$user['data']['password']==$current_pwd){
                    $user_mdl->begin_trans();
                    $new_pwd = $user_mdl->encode_pwd($request['new_pwd']);
                    $info = $_SESSION;
                    $info['password'] = $new_pwd;
                    $info['is_strong'] = 2;
                    if(preg_match("/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/", $request['new_pwd']) == true){
                    $info['is_strong'] = 1;
            		}
                    $ret = $user_mdl->update($info, $_SESSION['user_id']);
                    if ($ret['status'] < 1) {
                            $user_mdl->rollback();
			    echo json_encode($ret);
                    }else{
                            //修改后的密码同步到云中心
                            if(defined("CLOUD")&&CLOUD){
                                    $result = load_model('cloud_api/CloudModel')->send("?app_act=api/kehu_login/change_pwd",$info);
                                    if($result['status']!=1){
                                            $user_mdl->rollback();
                                            if($result['message']==''){
                                                    exit_json_response(-1,array(),"写入云中心出错");
                                            }else{
                                                    echo json_encode($result);exit;
                                            }
                                    }
                            }
                            $user_mdl->commit();
                                //去掉强制判断
                               $is_strong = CTX()->get_session('no_strong');
                                if(!empty($is_strong)){
                                      CTX()->set_session('no_strong',null);
                                }



                            exit_json_response(1,array(),"修改成功");
                    }
            }else{
                    exit_json_response(-1,array(),"当前密码不正确");
            }

    }
            function get_new_pwd($password){
                        $new_password = substr(base64_decode($password), 3);
                        return base64_decode($new_password);
            }
            function check_pwd($request){
            $current_pwd=$new_pwd=$sure_pwd='';
            if(isset($request['current_pwd'])&&$request['current_pwd']!=''){
                    $current_pwd = $request['current_pwd'];
            }else{
                    exit_json_response(-1,array(),"当前密码不能为空");
            }
            if(isset($request['new_pwd'])&&$request['new_pwd']!=''){
                    $new_pwd = $request['new_pwd'];
            }else{
                    exit_json_response(-1,array(),"新密码不能为空");
            }
            if(isset($request['sure_pwd'])&&$request['sure_pwd']!=''){
                    $sure_pwd = $request['sure_pwd'];
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
                $request['psw_strong'] = 1;
            }


            if($request['psw_strong'] == 1){
            if(preg_match("/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/", $new_pwd) == false){
                    exit_json_response(-1,array(),"密码须为数字、大写字母、小写字母和特殊符号的组合");
            }}
            if($request['psw_strong'] == 0){
    		if(preg_match("/^(?=.*\d)(?=.*[a-zA-Z]).{8,20}$/", $new_pwd) == false ){
                    exit_json_response(-1,array(),"密码须为数字和字母的组合");
            }}





    }


    function save_auth_shop(array & $request, array & $response, array & $app){
        $ret = load_model('base/ShopAuthModel')->save_auth_shop($request);
        header("Content-type: text/html; charset=utf-8");
        echo $ret['message'];
        die;
    }
    function save_auth_shop_info(array & $request, array & $response, array & $app){
        $ret = load_model('base/ShopAuthModel')->save_auth_shop_info($request);
        header("Content-type: text/html; charset=utf-8");
        echo $ret['message'];
        die;
        //?app_act=index/save_auth_shop_info
    }

    function login_by_auth_key(array & $request, array & $response, array & $app){
        $ret = load_model('sys/EfastAuthModel')->login_by_auth_key($request);
        if($ret['status']<0){
            echo $ret['message'];
            die;
        }else{
              echo "<script>location.href='{$ret['data']}';</script>";die;
        }

//        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?app_act=sys/auto_create/param_list";
//        echo "<script>location.href='{$url}';</script>";
//        die;
    }

    function login_by_form(array & $request, array & $response, array & $app){
        //error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
         $ret = load_model('common/OspSaasModel')->osp_login_change($request);

        if($ret['status']<0){
            $response =  $ret;
        }else{
            $response = load_model('sys/EfastUserModel')->login_by_form($ret['data']);
        }
        //$parse_req = load_model('common/CryptReqModel')->get($request);
        $req_data = $ret['data'] ;
        $check =  isset($req_data['check'])?$req_data['check']:0;
        if($check == 1){
            echo json_encode($response);
            die;
        }
   		$sql = "select lastchanged,is_strong,status from sys_user where user_code = '".$req_data['user_code']."'";
			$val = ctx()->db->getRow($sql);

			$arr = array('psw_strong','psw_period');
			$ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
                            $psw_strong =isset($ret_arr['psw_strong'])?$ret_arr['psw_strong']:'' ;
			$psw_period =isset($ret_arr['psw_period'])?$ret_arr['psw_period']:'' ;

		 	$current = date("Y-m-d H:i:s",time());
			$month=floor((strtotime($current)-strtotime($val['lastchanged']))/86400/30);
                        $is_strong_safe = CTX()->get_app_conf('is_strong_safe');
                        if($is_strong_safe){
                            $psw_strong = 1;
                        }

                        if($val['is_strong'] == 2&&$psw_strong == 1){
                            $response['status'] = -5;
                            CTX()->set_session('no_strong',1);
                        }


			if($val['is_strong'] == 0)
			$response['status'] = -6;

			if($month >= $psw_period)
			$response['status'] = -7;

//			$sql = "select * from sys_user ";
//			$val = ctx()->db->getAll($sql);
//			if(empty($val['data']))
//			$response['status'] = -8;

			if($val['status'] == 0)
			$response['status'] = -9;

         //app登录
         if(isset($request['is_app'])&&$request['is_app']==1){
                $login_code = array('2','-5','-6','-7','-8');
                if(in_array($response['status'],$login_code)){
                       $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?app_act=app/index/do_index";
                        echo "<script>location.href='{$url}';</script>";
                        die;
                }
         }

        if((int)$response['status'] == 2){
            $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
            echo "<script>location.href='{$url}';</script>";
            die;
        }
        if($response['status'] == -5){
            $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
            echo "<script>location.href='{$url}?app_act=index/change_password&reason=psw_strong';</script>";
            die;
        }
        if($response['status'] == -6){
        	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
            echo "<script>location.href='{$url}?app_act=index/change_password&reason=first_login';</script>";
            die;
        }
        if($response['status'] == -7){
        	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
            echo "<script>location.href='{$url}?app_act=index/change_password&reason=psw_period';</script>";
            die;
        }
        if($response['status'] == -8){
        	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
            echo "<script>location.href='{$url}?app_act=sys/auto_create/param_list';</script>";
            die;
        }
        echo json_encode($response);
        die;
    }

	function construct(array & $request, array & $response, array & $app) {

	}

}