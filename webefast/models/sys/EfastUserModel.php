<?php
/**
 * 用户相关业务
 *
 * @author huanghy
 *
 */
require_model('tb/TbModel');
require_lang('sys');

class EfastUserModel extends TbModel {

    public function __construct() {
    	parent::__construct();
    }
    private $app_key = '';
    function get_role_list($user_id, $filter) {
		if (empty($user_id)) {
			return $this->format_ret(OP_ERROR);
		}
		$select = '*';
		$sql_main = "FROM sys_user_role sur,sys_role sr where sur.role_id=sr.role_id and sur.user_id={$user_id}";
		$data =  $this->get_page_from_sql($filter, $sql_main, $select);
		//echo '<hr/>data<xmp>'.var_export($data,true).'</xmp>';
		foreach($data['data'] as $k=>$sub_data){
			$data['data'][$k]['role_code_txt'] = $sub_data['role_code'].'<input type="hidden" value="'.$sub_data['role_id'].'"/>';
		}
		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		filter_fk_name($ret_data['data'], array('role_id'));

		return $this->format_ret($ret_status, $ret_data);
	}

	function encode_pwd($pwd) {
		return md5(md5($pwd).$pwd);
	}

	function chk_login($user,$pwd_is_encode = 0){
        $sql = "select * from sys_user where user_code = :user_code AND status=1";
        $row = ctx()->db->get_row($sql,array('user_code'=>$user['user_code']));
		if (empty($row)) {
			return $this->format_ret(-1,'','user_not_found');
		}
        //获取用户角色
        if($pwd_is_encode == 1){
            $pwd = $user['password'];
        }else{
		    $pwd = $this->encode_pwd($user['password']);
        }

		if ($pwd != $row['password']) {
			//登录失败，记录密码输错的次数
			$sql = "update sys_user set login_fail_num = login_fail_num+1 where user_code = :user_code";
            ctx()->db->query($sql,array('user_code'=>$user['user_code']));
            $login_fail_num = $row['login_fail_num']+1;
			return $this->format_ret('password_invalid',array('login_fail_num'=>$login_fail_num));
		}
		//登录成功，密码输错次数清0
		$sql = "update sys_user set login_fail_num = 0 where user_code = :user_code";
		ctx()->db->query($sql,array('user_code'=>$user['user_code']));

                  $user['tel'] = $this->get_tel($row['phone']);

		return $this->format_ret(1,array('tel'=>$user['tel'] ));
	}

    /**
     * 获取请求客户端ip
     */
    function get_client_ip(){
        if(!empty($_SERVER["argv"])) {
            $cip = '127.0.0.1';
        } elseif(!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
            $cip = $_SERVER["REMOTE_ADDR"];
        } else {
            $cip = "无法获取！";
        }
        return $cip;
    }

	// 判断用户是否已经登陆
	function _is_already_login($user, $khid='def') {
	    if (CTX()->get_app_conf('store_session_in_cache') == false) { // 如果session不保存在缓存中，什么都不做
	        return false;
	    }
		$cache = app_get_cache('session');

		$suid = $cache->get('session-id-'.$user.'-'.$khid);


        if ($suid === FALSE || $cache->get($suid) === FALSE
		        || strpos($cache->get($suid), 'user_id') === FALSE) {
		            // user map not exist or user session not exist
			return false;
        }

		return true;
	}
	// 登陆成功之后设置此信息
	function _set_login_ext($user, $khid='def') {
	    if (CTX()->get_app_conf('store_session_in_cache') == false) {// 如果session不保存在缓存中，什么都不做
	        return ;
	    }
        $cache = app_get_cache('session');
        $cache->set('session-id-'.$user.'-'.$khid, session_id());
	}
	function login($user,$pwd_has_encode = 0,$kh_id = 0){
		if ($this->_is_already_login($user['user_code'], $kh_id)) {
			return $this->format_ret('user_already_login');
		}
		$ret = $this->chk_login($user,$pwd_has_encode);
		if ($ret['status']!=1){
			return $ret;
		}
        $sql = "select * from sys_user where user_code = :user_code";
        $row = ctx()->db->get_row($sql,array('user_code'=>$user['user_code']));
		if (empty($row)) {
			return $this->format_ret('user_not_found');
		}
		//echo '<hr/>row<xmp>'.var_export($row,true).'</xmp>';
		$role = $this->get_role_list($row['user_id'],array());
		CTX()->set_session('user_id', $row['user_id'], true);
		CTX()->set_session('user_code', $row['user_code'], true);
		CTX()->set_session('user_name', $row['user_name'], true);
		CTX()->set_session('login_time', date('Y-m-d H:i:s'), true);
                CTX()->set_session('role', $role['data'], true);
                CTX()->set_session('kh_id', $kh_id, true);
                CTX()->set_session('login_type', $row['login_type'], true);
                $user['tel'] = $this->get_tel($row['phone']);



		$this->_set_login_ext($user['user_code'], $kh_id);

		//添加登陆日志start
                require_lib('comm_util', true);
                $sever_ip = server_ip();
		$log = array('type'=>'0','user_id'=>$row['user_id'],'user_code'=>$row['user_code'],'ip'=>$this->get_client_ip(),'add_time'=>date('Y-m-d H:i:s'),'server_ip'=>$sever_ip);
		$ret1 = load_model('sys/LoginLogModel')->insert($log);
		//添加登陆日志end

        $role = ctx()->get_session('role');
        $role_code_arr = array();
        $is_manage = 0;

        if (!empty($role['data'])){
	        foreach($role['data'] as $sub_data){
	            if($sub_data['role_code'] == 'manage'){
	                $is_manage = 1;
	            }
	            $role_code_arr[] = $sub_data['role_code'];
	        }
        }
	 CTX()->set_session('is_manage', $is_manage, true);
        if(!empty($role_code_arr) && $is_manage == 0 && !in_array('oms_shop', $role_code_arr) ){
            $role_code_list = "'".join("','",$role_code_arr)."'";
            //业务类型 1：店铺 2：仓库 3：品牌
            $profession_type_map = array('1'=>'shop_code','2'=>'store_code','3'=>'brand_code');
            //添加权限
            $sql = "select relate_code,profession_type from sys_role_profession where role_code in($role_code_list)";
            $db_arr = ctx()->db->getAll($sql);
            if(!empty($db_arr)){
                $arr = array();
                foreach($db_arr as $sub_db){
                    $arr[$profession_type_map[$sub_db['profession_type']]][] = $sub_db['relate_code'];
                }
                foreach($arr as $code=>$sub_arr){
                    $code_list = join(',',$sub_arr);
                    CTX()->set_session($code, $code_list, true);
                }
            }
        }else if(in_array('oms_shop', $role_code_arr)){
            CTX()->set_session('oms_shop_code', $row['relation_shop'], true);
        }

        if(!empty($this->app_key)){
             CTX()->set_session('app_key',$this->app_key, true);
        }
        return $this->format_ret(1,$user);
	}

        private function get_tel($phone){
           $len =  strlen($phone);
           if($len<>11){
               $phone = '';
           }
           return $phone;
        }

        //自动取一个管理员账号进行登录
	function auto_admin_login($req_data){
        $sql = "select user_code,password from sys_user a,sys_role b where a.role_id = b.role_id and b.role_code = 'manage' and a.is_login = 1";
        $user_row = ctx()->db->get_row($sql);
        $user = array('user_code'=>$user_row['user_code'],'password'=>$user_row['password']);
        $ret = $this->login($user,1,$req_data['kh_id']);
        if($ret['status']!=1){
            return $this->format_ret(-1,'',$ret['message']);
        }
		return $ret;
	}

	function login_by_form($req_data){

      $is_check = isset($req_data['check']) ? $req_data['check'] : '';

          /*
        //echo '<hr/>$req_data<xmp>'.var_export($req_data,true).'</xmp>';die;
        //切换数据库链接
        $link = array(
            'name' => $req_data['rds_dbname'],
            'host' => $req_data['rds_link'],
            'user' => $req_data['rds_user'],
            'pwd' => $req_data['rds_pass'],
            'type' => 'mysql'
        );
        CTX()->set_session('link', $link, true);

        //echo '<hr/>$link<xmp>'.var_export($link,true).'</xmp>';die;
        CTX()->db->set_conf($link);
        */
        $req_data['app_key'] = isset($req_data['app_key'])?$req_data['app_key']:'';
        $this->set_app_key($req_data['app_key']);
        $user = array('user_code'=>$req_data['user_code'],'password'=>$req_data['password']);
        if($is_check == 1){
            $ret = $this->chk_login($user);
        }else{
            $ret = $this->login($user,0,$req_data['kh_id']);
            if($ret['status']>0){
                $GLOBALS['context']->set_cookie('login_server_url',$req_data['login_server_url'],42000);
                return $this->format_ret(2,$ret['data']);
            }
        }
        $this->set_cp_param($req_data);

	return $ret;
	}
        private function set_app_key($app_key){

               if(!empty($app_key)){

                   $ins = array('code'=>'app_key','name'=>'应用主key','value'=>$app_key);
                   $update_str = " value = VALUES(value) ";
                   $this->insert_multi_duplicate('sys_auth', array($ins), $update_str);
                   $this->app_key = $app_key;
               }


        }
        private function set_cp_param($req_data){
            if(isset( $req_data['cp_code'])){
                $info['cp_code'] = $req_data['cp_code'];
                $info['cp_area'] = isset($req_data['cp_area'])?$req_data['cp_area']:'online';
                $this->db->update('sys_auth', array('value'=>$info['cp_code']),array('code'=>'cp_code'));
                $this->db->update('sys_auth', array('value'=>$info['cp_area']),array('code'=>'cp_area'));
            }
            if(isset($req_data['auth_num'])){
                 $this->db->update('sys_auth', array('value'=>$req_data['auth_num']),array('code'=>'auth_num'));
            }
        }


    function get_strategytype($kh_id) {
        $filter = array(
            'kh_id' => $kh_id,
        );
        $ret = load_model('sys/sysServerModel')->osp_server('products.strategytype.list', array($filter));
        return $ret;
    }


}