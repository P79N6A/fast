<?php
/**
 * 用户相关业务
 *
 * @author huanghy
 *
 */
require_model('tb/TbModel');
require_lang('sys');

class EfastAuthModel extends TbModel {
    
    public $app_info;
    
    public function __construct() {
    	parent::__construct();
        $this->app_info['taobao'] = array('app_key'=>'12651526','app_secret'=>'11b9128693bfb83d095ad559f98f2b07'); 
	$this->app_info['jingdong'] = array('app_key'=>'C0C9110ED2D32E13266D4522D55C78AF','app_secret'=>'f58736a321bd44fabf2253f5d25344f0'); 
        $this->app_info['yihaodian'] = array('app_key'=>'10210015011400002866','app_secret'=>'6b2109abe40979c4acb41dbfc9068724'); 
    }

    function init_sys_auth($info){
        $sql = "truncate table sys_auth";
        ctx()->db->query($sql);
        $ins = array();
        $ins['version'] = array('code'=>'version','name'=>'版本号');
        $ins['company_name'] = array('code'=>'company_name','name'=>'授权公司名称');
        $ins['auth_key'] = array('code'=>'auth_key','name'=>'授权产品密钥');
        $ins['auth_num'] = array('code'=>'auth_num','name'=>'授权注册用户');
        $ins['cp_code'] = array('code'=>'cp_code','name'=>'产品类型代码');
        $ins['login_server_url'] = array('code'=>'login_server_url','name'=>'登录服务器的URL');
        $ins['kh_id'] = array('code'=>'kh_id','name'=>'客户ID号');
        $ins['app_key'] = array('code'=>'app_key','name'=>'应用主key');
        $ins['cp_area'] = array('code'=>'cp_area','name'=>'业务范围');
        
        foreach($ins as $key=>$sub_ins){
            $sub_ins['value'] = $info[$key];
            $ret = M('sys_auth')->insert($sub_ins);
        }
        return $ret;
    }

	function login_by_auth_key($req){
        $ret = load_model('common/OspSaasModel')->osp_login_change($req);
        if($ret['status']<0){
            return $ret;
        }
        $this->db = CTX()->db;
        /*
        array (
          'shop_user_nick' => 'hhysc',
          'shop_title' => '唐江镇',
          'access_token' => '70012100b35544983f54a88ab017998365a6cee3f50d5e31ad73ddd8a9052d8d122f34610468158',
          'refresh_token' => '70013100735167866ae86b6c7dbd7e498f8a85f682e23091333751534d0fbe394f6ff7910468158',
          'expires_in' => 30915808,
          'shop_type' => 'C',
          'db_name' => 'efast5.0.1',
          'is_init_sys' => 1,
          'auth_key' => '9A68ECA9-8D2E-5EA5-1BEA-ED41809C857C',
          'auth_num' => '10',
          'kh_name' => '上海百胜',
          'version' => 'v5.0.1',
          'login_server_url' => 'http://121.41.162.176/weblogin/web/index.php',
        )
        */
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';die;
        $req_data = $ret['data'];
        if((int)$req_data['kh_id']<=0){
            return $this->format_ret(-1,'','授权数据 缺少客户ID号');
        }

        $shop_user_nick = $req_data['shop_user_nick'];
        $shop_title = $req_data['shop_title'];
        $access_token = $req_data['access_token'];
        $refresh_token = $req_data['refresh_token'];
        $shop_type = $req_data['shop_type'];
        $expires_in = $req_data['expires_in'];
        $sd_expires_in = date('Y-m-d H:i:s',time()+$expires_in);

        //切换数据库链接
//        $link = array(
//            'name' => $req_data['rds_dbname'],
//            'host' => $req_data['rds_link'],
//            'user' => $req_data['rds_user'],
//            'pwd' => $req_data['rds_pass'],
//            'type' => 'mysql'
//        );

        //echo '<hr/>$req_data<xmp>'.var_export($req_data,true).'</xmp>';
        //echo '<hr/>$lisnk<xmp>'.var_export($link,true).'</xmp>';
        //die;
//        CTX()->set_session('link', $link, true);
//        CTX()->db->set_conf($link);
        $app_info = require_conf('sys/app_info');
        //使用翼商的KEY
       $req_data['app_key'] = isset($req_data['app_key'])?$req_data['app_key']:'12651526';
        if($req_data['sale_channel_code']=='taobao'){
            $auth_arr = $app_info['taobao'][$req_data['app_key']];
  
        }else{
             $auth_arr = isset($app_info[$req_data['sale_channel_code']])?$app_info[$req_data['sale_channel_code']]:array();
        }
        
 
        if (empty($auth_arr)){
             return $this->format_ret(-1,'',$req_data['sale_channel_code'].' 缺少appkey配置');	        
        }
        $auth_arr['refresh_token']=$refresh_token;
        $auth_arr['session']=$access_token;
        $auth_arr['nick']=$shop_user_nick;
        $auth_arr['shop_type']=$shop_type;
	$auth_arr['type']=$shop_type;

        //$auth_arr = array('app_key'=>'12651526','app_secret'=>'11b9128693bfb83d095ad559f98f2b07','refresh_token'=>$refresh_token,'session'=>$access_token,'nick'=>$shop_user_nick,'shop_type'=>$shop_type);
            
        $auth_json = json_encode($auth_arr);
//$shop_user_nick
       $shop_code= $this->get_shop_code_by_shop_nick($shop_user_nick,$req_data['sale_channel_code']);

        $ins_row = array('shop_code'=>$shop_code,'shop_user_nick'=>$shop_user_nick,'shop_name'=>$shop_title,'sale_channel_code'=>$req_data['sale_channel_code'],'authorize_state'=>1,'days'=>1,'authorize_date'=>$sd_expires_in,'is_active'=>1);
       
        $sql_store = "SELECT store_code from base_store order by store_id asc";
        $store_code = $this->db->get_value($sql_store);
        
        $ins_row['send_store_code'] = $store_code;
         $ins_row['refund_store_code'] = $store_code;
         $ins_row['stock_source_store_code'] = $store_code;
        M('base_shop')->insert_dup($ins_row,'UPDATE','authorize_date');
        
        //            'source'=>$req_data['sale_channel_code'],
//                'app_key'=>$auth_arr['app_key'],
//            'app_secret'=>$auth_arr['app_secret'],

        
        $ins_row = array(
            'source'=>$req_data['sale_channel_code'],
            'shop_code'=>$shop_code,
            'api'=>$auth_json,
            'tb_shop_type'=>$shop_type,
            'nick'=>$shop_user_nick,
            'app_key'=>$auth_arr['app_key'],
            'app_secret'=>$auth_arr['app_secret'],
            'session_key'=>$access_token,
            'kh_id'=>$req_data['kh_id'],
        );
        M('base_shop_api')->insert_dup($ins_row,'UPDATE','nick,api,tb_shop_type,app_key,app_secret,session_key,kh_id');

        load_model('sys/security/SysEncrypModel')->create_shop_encrypt($shop_code);
        
        //添加系统日志
        $module = '网络店铺'; //模块名称
        $operate_type = '新增'; //操作类型
        $log_xq = '新增店铺名称为:' . $shop_title . '的网络店铺,并完成授权,店铺自动开启;';
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module,  'operate_type' => $operate_type, 'operate_xq' => $log_xq);
        load_model('sys/OperateLogModel')->insert($log);

        if($req_data['is_init_sys'] == 1){
            $sql = "select value from sys_auth where code = 'auth_key'";
            $auth_key = ctx()->db->getOne($sql);
            if(!empty($auth_key)){
                return $this->format_ret(-1,'','此数据库已有授权信息');
            }
            if(empty($req_data['login_user_name']) || empty($req_data['login_password'])){
                return $this->format_ret(-1,'','初始化账号密码缺失');
            }
            $ret = $this->set_init_sys_end($req_data['auth_key'],$req_data['login_server_url']);
            if($ret['status']<0){
                $ret['message'] .= '设置运营平台客户授权完成标识失败';
                return $ret;
            }
            //sys_user
            $sql = "select role_id from sys_role where role_code = 'manage'";
            $role_id = ctx()->db->getOne($sql);
            $pwd = load_model('sys/EfastUserModel')->encode_pwd($req_data['login_password']);
            $ins = array(
                'role_id'=>$role_id,
                'user_code'=>$req_data['login_user_name'],
                'user_name'=>$req_data['login_user_name'],
                'password'=>$pwd,
                'status'=>1,
                'is_login'=>1,
                'is_manage'=>1
                );
            $ret = M('sys_user')->insert_dup($ins);
            if($ret['status']<0){
                return $ret;
            }

            $sql = "select user_id from sys_user where user_code = '{$req_data['login_user_name']}'";
            $user_id = ctx()->db->getOne($sql);

            $sql = "select role_id from sys_role where role_code = 'manage'";
            $role_id = ctx()->db->getOne($sql);
            $ins = array(
                'role_id'=>$role_id,
                'user_id'=>$user_id
                );
            //echo '<hr/>$ins<xmp>'.var_export($ins,true).'</xmp>';
            $ret = M('sys_user_role')->insert_dup($ins);
            if($ret['status']<0){
                return $ret;
            }

            $info = array();
            $info['version'] = $req_data['version'];
            $info['company_name'] = $req_data['kh_name'];
            $info['auth_key'] = $req_data['auth_key'];
            $info['auth_num'] = $req_data['auth_num'];
            $info['cp_code'] = $req_data['cp_code'];
            $info['login_server_url'] = $req_data['login_server_url'];
            $info['kh_id'] = $req_data['kh_id'];
            $info['app_key'] = $req_data['app_key'];
            $info['cp_area'] = isset($req_data['cp_area'])?$req_data['cp_area']:'online';
            
            

            $ret = $this->init_sys_auth($info);
            if($ret['status']<0){
                return $ret;
            }
        }
        $GLOBALS['context']->set_cookie('login_server_url',$req_data['login_server_url'],42000);
		//自动登录
       // $ret = load_model('sys/EfastUserModel')->auto_admin_login($req_data);
		return $this->format_ret(1,$req_data['login_server_url']);
	}
    
        
    function  get_shop_code_by_shop_nick($shop_nick,$source){

        
        $sql = "select shop_code from base_shop_api where source=:source AND  nick = :nick ";
        $shop_code = $this->db->get_value($sql,array(':source'=>$source,':nick'=>$shop_nick));
        if(empty($shop_code)){
            $shop_code = $shop_nick;
        }
        return $shop_code;
        
    }
                
    function set_init_sys_end($auth_key,$login_server_url){
        $data = array('auth_key'=>$auth_key);
        require_model('common/CryptReqModel');
        $obj = new CryptReqModel();
        $req_arr = $obj->create($data);
        $req_arr['app_act'] = 'index/set_init_sys_end';
        $url = $login_server_url.'?'.http_build_query($req_arr);
        //echo $url."<br/>";//die;
        $url_resp = $this->do_execute($url,array());
        //echo '<hr/>$url_resp<xmp>'.var_export($url_resp,true).'</xmp>';
        $resp = json_decode($url_resp,true);
        if((int)$resp['status']<=0){
            return $this->format_ret(-1,'','设置系统完成初始化状态出错');
        }
        return $this->format_ret(1);
    }

    function do_execute($url, $params) {
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_FAILONERROR, false );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 3);
        /**
         * 如果参数为数组则
         */
        if (is_array ( $params ) && 0 < count ( $params )) {
            $postBodyString = "";
            foreach ( $params as $k => $v ) {
                $postBodyString .= "$k=" . urlencode ( $v ) . "&";
            }
            unset ( $k, $v );
        } else {
            $postBodyString = $params;
        }
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, substr ( $postBodyString, 0, - 1 ) );

        $reponse = curl_exec ( $ch );
        if (curl_errno ( $ch )) {
            $curl_error = curl_error ( $ch );
            throw new Exception ( $curl_error, 0 );
        } else {
            $httpStatusCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
            if (200 !== $httpStatusCode) {
                throw new Exception ( $reponse, $httpStatusCode );
            }
        }
        curl_close ( $ch );

        return $reponse;
    }

}