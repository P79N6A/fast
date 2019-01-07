<?php
/**
 * 用户相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');
require_lang('sys');
require_lib('net/HttpEx');
class UserModel extends TbModel {

    public function __construct() {
    	parent::__construct('sys_user', 'user_id');
    }

    //状态2 在分销审核中 应用
    public $status = array(
        0 => '审核不通过',
        1 => '审核通过',
        2 => '未审核',
    );
    /*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {
	    $sql_values = array();
		$sql_join = "";
		$sql_main = "FROM {$this->table} t $sql_join WHERE 1 AND type=0 AND login_type=0";
		if (isset($filter['is_buildin']) && $filter['is_buildin']!='' ) {
			$sql_main .= " AND is_buildin=:is_buildin";
			$sql_values[':is_buildin'] = $filter['is_buildin'];
		}
		//关键字
		if (isset($filter['keyword']) && $filter['keyword']!='' ) {
		    $sql_main .= " AND (t.user_code LIKE :keyword or t.user_name LIKE :keyword)";
			$sql_values[':keyword'] = '%'.$filter['keyword'].'%';
		}

 		if (isset($filter['status']) && $filter['status']!='' ) {
 		    $sql_main .= " AND t.status=:status";
 		    $sql_values[':status'] = $filter['status'];
 		}

		$select = 't.*';

		$data =  $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
                $delete_auth = load_model('sys/PrivilegeModel')->check_priv('sys/user/do_delete');
                foreach ($data['data'] as &$value) {
                    $value['delete_auth'] = $delete_auth == '' ? 0 : 1;
                    $sql = "SELECT * FROM sys_user_role sur,sys_role sr where sur.role_id=sr.role_id and sur.user_id=:user_id";
                    $sql_value = array(':user_id' => $value['user_id']);
                    $role_data = $this->db->get_all($sql,$sql_value);
                    foreach($role_data as $role){
                        $value['role_name'][] = $role['role_name'];
                    }
                    if(isset($value['role_name'])){
                        $value['role_name'] = implode(',', $value['role_name']);
                    }else{
                         $value['role_name'] = '';
                    }
                }
		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);
	}

    /*
     * 根据条件查询店员数据
     */

    function get_clerk_by_page($filter) {
        if (!isset($filter['status']) || !in_array($filter['status'],array(0, 1, ''))) {
            $filter['status'] = 1;
        }
        $sql_values = array();
        $sql_join = "LEFT JOIN base_shop bs ON bs.shop_type=1 AND t.relation_shop=bs.shop_code";
        $sql_main = "FROM {$this->table} t $sql_join WHERE 1 AND t.type>0";
        //关键字
        if (isset($filter['keyword']) && $filter['keyword'] != '') {
            $sql_main .= " AND t.{$filter['keyword_type']} LIKE :keyword";
            $sql_values[':keyword'] = '%' . $filter['keyword'] . '%';
        }

        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND t.status=:status";
            $sql_values[':status'] = $filter['status'];
        }

        $login_type = CTX()->get_session('login_type');
        if ($login_type == 1) {
            $sql_main .= ' AND t.relation_shop=:shop_code';
            $sql_values[':shop_code'] = CTX()->get_session('oms_shop_code');
        }

        $select = 't.user_id,t.user_code,t.user_name,t.type,t.phone,t.sex,t.status,bs.shop_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $clerk_park = ds_get_field('parttype', 0);
        $clerk_sex = ds_get_field('usersex', 0);
        foreach ($data['data'] as &$value) {
            $value['type'] = $clerk_park[$value['type']];
            $value['sex'] = $clerk_sex[$value['sex']];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /*
	function get_by_id($id) {
		return  $this->get_row(array('user_id'=>$id));
	}*/

	/*
	 * 添加新纪录
	 */
	function insert($user) {
		$status = $this->valid($user);
		if ($status < 1) {
			return $this->format_ret($status);
		}

		$ret = $this->is_exists($user['user_code']);
		if ($ret['status'] > 0 && !empty($ret['data'])) return $this->format_ret('user_error_unique_code');

		$ret = $this->is_exists($user['user_name'], 'user_name');
		if ($ret['status'] > 0 && !empty($ret['data'])) return $this->format_ret('user_error_unique_name');
		return parent::insert($user);
	}

    /**
     * 添加店员
     */
    function insert_user($user) {
        $pwd = 'baota8888';
        $user['password'] = $this->encode_pwd($pwd);
        $user['create_time'] = date('Y-m-d h:i:s');
        $user['create_person'] = CTX()->get_session('user_name');
//        $user['login_type'] = 1;
        $status = $this->valid($user);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($user['user_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) {
            return $this->format_ret('user_error_unique_code');
        }
        $ret_user =  parent::insert($user);
        if($ret_user['status']>0){
            if($user['login_type']==1){
                $this->create_shop_role($ret_user['data']);
            }else if($user['login_type']==2){
                $this->create_custom_role($ret_user['data']);
            }
        }
        return $ret_user;
    }

    /**
     * 更新启用状态
     * @param string $active 状态
     * @param int $id 用户ID
     */
    function update_active($active, $id, $type = 0) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }

        if ($active == 1 && $type == 0) {
            $sql = "select count(*) as sum from sys_user where status = 1";
            $res = ctx()->db->getRow($sql);

            $sql = "select value from sys_auth where code = 'auth_num'  ";
            $arr = ctx()->db->getRow($sql);

            if ($res['sum'] >= $arr['value']){
                return $this->format_ret('pass_num');
            }
        }
        $ret = parent::update(array('status' => $active), array('user_id' => $id));
        return $ret;
    }

    /*
         *删除记录
         */
        function delete($id){
            $table = $this->table;
            $ret = parent::delete(array('user_id'=>$id));
            if($ret['status'] == 1) {
                //根据id删除用户的角色信息
                $this->delete_exp('sys_user_role', array('user_id'=>$id));
            }
            return $ret;
        }

	/*
	 * 修改纪录
	 */
	function update($user, $id) {
		$status = $this->valid($user, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}

		$ret = $this->get_row(array('user_id'=>$id));
		if($user['user_name'] != $ret['data']['user_name']){
			$ret = $this->is_exists($user['user_name'], 'user_name');
			if ($ret['status'] > 0 && !empty($ret['data'])) return $this->format_ret(USER_ERROR_UNIQUE_NAME);
		}
		$ret = parent::update($user, array('user_id'=>$id));
		return $ret;
	}

	/*
	 * 判断角色代码是否唯一
	 */
	private function is_unique($user_code) {
		$ret = $this->get_row( array('user_code'=>$user_code));

		$status = $ret['status'] == 1 ? USER_ERROR_UNIQUE_CODE : $ret['status'];

		return $this->format_ret($status);
	}

	/*
	 * 服务器端验证
	 */
	private function valid($data, $is_edit = false) {
		if (!$is_edit && (!isset($data['user_code']) || !valid_input($data['user_code'], 'required'))) return USER_ERROR_CODE;
		if (!isset($data['user_name']) || !valid_input($data['user_name'], 'required')) return USER_ERROR_NAME;

		return 1;
	}

	private function is_exists($value, $field_name='user_code') {
		$ret = parent::get_row(array($field_name=>$value));

		return $ret;
	}

    /**
     * 重设密码
     * @param int $user_id 用户ID
     * @param int $type 用户类型 0-普通用户，>0-店员
     */
    function reset_pwd($user_id, $type = 0) {
        if ($type == 0) {
            $pwd = $this->generatePassword();
            $value = array('password' => $this->encode_pwd($pwd), 'is_strong' => 2);
        } else {
            $pwd = 'baota8888';
            $value = array('password' => $this->encode_pwd($pwd));
        }

        $ret = parent::update($value, array('user_id' => $user_id));
        if ($ret['status'] < 1) {
            return $ret;
        }
        return $this->format_ret($ret['status'], $pwd);
    }

    function generatePassword($length=8){
          $chars = array_merge(range(0,9),
                            range('a','z'),
                     range('A','Z'),
                    array('!','@','$','%','^','&','*'));
                shuffle($chars);
                $password = '';
                for($i=0; $i<8; $i++) {
                    $password .= $chars[$i];
                }
            if(preg_match("/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/", $password) == true){
                        return $password;
            }
            return $this->generatePassword($length);

        }
	function encode_pwd($pwd) {
		return md5(md5($pwd).$pwd);
	}

	function get_top_menu() {
		$arr_nav_cote = array();

		$cid_list = array();
		$no_priv_arr = array();

		$sql = "SELECT action_id, action_name, action_code, sort_order";
		$sql .= " FROM sys_action WHERE type='cote' AND parent_id = 0 AND status = 1 ORDER BY sort_order ASC";
		$rs = $this->db->get_all($sql);

		$rs_cote = array();
		foreach ( $rs as $cote ) {
			$rs_cote[$cote['action_id']] = $cote;
		}

		return $rs_cote;
	}
	function get_menu_tree() {
		$sql = "SELECT action_id, action_name, action_code, sort_order, parent_id, type";
		$sql .= " FROM sys_action WHERE status = 1 ORDER BY sort_order ASC";
		$rs = $this->db->get_all($sql);

		$menu_cote = $this->array_search_by_key($rs, 'type', 'cote');

		$ret = array();
		foreach ($menu_cote as &$cote) {
			$cote['_child'] = $this->array_search_by_key($rs, 'parent_id', $cote['action_id']);
			foreach ($cote['_child'] as &$group) {
				$group['_child'] = $this->array_search_by_key($rs, 'parent_id', $group['action_id']);
			}
		}

		return $menu_cote;
	}

	private function array_search_by_key($arr, $key, $keyvalue) {
		$rs = array();
		foreach ($arr as $v) {
			if ($v[$key] == $keyvalue) {
				$rs[] = $v;
			}
		}

		return $rs;
	}

	function get_role_list($user_id, $filter) {
		if (empty($user_id)) {
			return $this->format_ret(OP_ERROR);
		}
		$select = '*';
		$sql_main = "FROM sys_user_role sur,sys_role sr where sur.role_id=sr.role_id and sur.user_id={$user_id} and role_code not in('oms_shop','distribution')";
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

	function get_role_list_noset($user_id,$filter){
		//echo '<hr/>$user_id<xmp>'.var_export($user_id,true).'</xmp>';die;
		if (empty($user_id)) {
			return $this->format_ret(OP_ERROR);
		}
		$wh = '';
                $sql_values = array();
		if (!empty($filter['keyword'])){
			$wh = "(role_code like :role_code or role_name like :role_code ) and ";
                        $sql_values[':role_code'] = "%{$filter['keyword']}%";
		}
		$user_id  = (int)$user_id;
		$select = '*';
		$sql_main = "from sys_role where {$wh} role_id not in(select role_id from sys_user_role where user_id = {$user_id}) and role_code not in('oms_shop','distribution')";
		//echo $sql_main;die;
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		foreach($data['data'] as $k=>$sub_data){
			$data['data'][$k]['role_code_txt'] = $sub_data['role_code'].'<input type="hidden" value="'.$sub_data['role_id'].'"/>';
		}

		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}

	function chk_login($user){
		$ret = $this->get_row(array('user_code'=>$user['user_code']));
		if ($ret['status'] < 1) {
			return $this->format_ret('user_not_found');
		}
		$row = $ret['data'];
        //获取用户角色
		$pwd = $this->encode_pwd($user['password']);

		/*
		echo '<hr/>pwd<xmp>'.var_export($pwd,true).'</xmp>';
		echo '<hr/>row<xmp>'.var_export($row,true).'</xmp>';
		die;*/
		if ($pwd != $row['password']) {
			return $this->format_ret('password_invalid');
		}
		return $this->format_ret(1);
	}

	//从运营平台登录的入口
	function login_enter($params){
		//验证参数有效性
		$secret = '4rXm3QQW30Y4EkVEiuJ5CapgAG$xXsh$';
		$timestamp = $params['timestamp'];
		$sign = $params['sign'];
		$chk_tag = $params['chk_tag'];
		unset($params['sign']);
		ksort($params);
		$js_sign = md5(join('_',$params).'_'.$secret);
		//echo '<hr/>params<xmp>'.var_export($params,true).'</xmp>';
		//echo '<hr/>js_sign<xmp>'.var_export($js_sign,true).'</xmp>';
		//echo '<hr/>sign<xmp>'.var_export($sign,true).'</xmp>';

		if ($js_sign != $sign){
			return $this->format_ret(-1,'','签名验证失败');
		}
		if (time() - $timestamp>60){
			return $this->format_ret(-1,'','请求时间戳误差不得超过60秒');
		}
		$user = array();
		require_model('common/CryptModel');
		$crypt_mdl = new CryptModel($timestamp);
		$user['user_code'] = $crypt_mdl->ecb_dencrypt($params['user_code']);
		$user['password'] =  $crypt_mdl->ecb_dencrypt($params['password']);
		//$user['user_code'] = $params['user_code'];
		//$user['password'] =  $params['password'];

		//echo '<hr/>user<xmp>'.var_export($user,true).'</xmp>';
		//echo '<hr/>chk_tag<xmp>'.var_export($chk_tag,true).'</xmp>';
		if ($chk_tag == 1){
			$ret = $this->chk_login($user);
		}else{
			$ret = $this->login($user);
		}
		return $ret;
	}

	// 临时实现，待调整
	function login($user) {
		$ret = $this->chk_login($user);

		if ($ret['status']!=1){
			return $ret;
		}
		$ret = $this->get_row(array('user_code'=>$user['user_code']));
		if ($ret['status'] < 1) {
			return $this->format_ret('user_not_found');
		}
		$row = $ret['data'];
		//echo '<hr/>row<xmp>'.var_export($row,true).'</xmp>';
		$role = $this->get_role_list($row['user_id'],array());
		CTX()->set_session('user_id', $row['user_id'], true);
		CTX()->set_session('user_code', $row['user_code'], true);
		CTX()->set_session('user_name', $row['user_name'], true);
		CTX()->set_session('login_time', date('Y-m-d H:i:s'), true);
        CTX()->set_session('role', $role['data'], true);
        CTX()->set_session('kh_code', 'ys_efast5', true);//ys_efast5 为临时测试增加


		//添加登陆日志start
		$log = array('type'=>'0','user_id'=>$row['user_id'],'user_code'=>$row['user_code'],'ip'=>get_client_ip(),'add_time'=>date('Y-m-d H:i:s'));
		$ret1 = load_model('sys/LoginLogModel')->insert($log);
		//添加登陆日志end
		return $this->format_ret(1);
	}

    /**
     * 从云中心登录session处理，默认已经验证有效性
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-22
     * @param string $kh_code 商户代码
     * @param string $user_code 用户代码
     * @return array 返回成功标识
     */
    function login_by_cloud($kh_code, $user_code){
        $ret = $this->get_row(array('user_code'=>$user_code));
        if ($ret['status'] < 1) {
			return $this->format_ret(-1,array(),'用户不存在!');
		}
        $row = $ret['data'];
        //获取用户角色
        $role = $this->get_role_list($row['user_id'],array());
        CTX()->set_session('user_id', $row['user_id'], true);
		CTX()->set_session('user_code', $row['user_code'], true);
		CTX()->set_session('user_name', $row['user_name'], true);
		CTX()->set_session('login_time', date('Y-m-d H:i:s'), true);
        CTX()->set_session('kh_code', $kh_code, true);
        CTX()->set_session('role', $role['data'], true);
        //添加登陆日志start
		$log = array('type'=>'0','user_id'=>$row['user_id'],'user_code'=>$row['user_code'],'ip'=>get_client_ip(),'add_time'=>date('Y-m-d H:i:s'));
		load_model('sys/LoginLogModel')->insert($log);
		//添加登陆日志end
		return $this->format_ret(1);
    }

	function logout() {
		//添加退出日志
		$log = array();
		$log['type'] = '1';
		$log['user_id'] = CTX()->get_session('user_id');
		$log['user_code'] = CTX()->get_session('user_code');
		$log['ip'] = get_client_ip();
		$log['add_time'] = date('Y-m-d H:i:s');
                if($log['user_code']!==NULL){
		        $ret1 = load_model('sys/LoginLogModel')->insert($log);
                }
                CTX()->saas->out_saas();
		session_destroy();
	}

	function delete_role($user_id, $role_id_arr) {
	    $where = array();
	    $model = M('sys_user_role');
	    foreach ($role_id_arr as $id) {
	        $where = array('user_id'=>$user_id, 'role_id'=>$id);
	        $model->delete($where);
	    }

	    return $this->format_ret(1,'','delete_success');
	}

	function add_role($user_id, $role_id_arr) {
	    $rs_new = array();
	    $model = M('sys_user_role');
	    foreach ($role_id_arr as $id) {
	        $rs_new[] = array('user_id'=>$user_id, 'role_id'=>$id);

	    }

	    return $model->insert($rs_new);
	}

	/**
	 * 分页查询用户登陆日志信息
	 * @param unknown $filter
	 * @return array PageData
	 */
	function get_loginlog_by_page($filter) {
	    $sql_join = "";
	    $sql_main = "FROM sys_user_loginlog t $sql_join WHERE 1";

	   	//关键字
	    if (isset($filter['keyword']) && $filter['keyword']!='' ) {
	        $sql_main .= " AND (t.user_code LIKE :keyword or t.user_name LIKE :keyword)";
	        $sql_values[':keyword'] = '%'.$filter['keyword'].'%';
	    }

	    if(isset($filter['__sort']) && $filter['__sort'] != '' ){
	        $filter['__sort_order'] = $filter['__sort_order'] =='' ? 'asc':$filter['__sort_order'];
	        $sql_main .= ' order by '.trim($filter['__sort']).' '.$filter['__sort_order'];
	    }
	    $select = 't.*';

	    $data =  $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

	    $ret_status = OP_SUCCESS;
	    $ret_data = $data;

	    return $this->format_ret($ret_status, $ret_data);
	}

	function user_remove_role($user_id,$role_id_list){
		$user_id = (int)$user_id;
		$sql = "delete from sys_user_role where user_id = $user_id and role_id in($role_id_list)";
		$ret = CTX()->db->query($sql);
		return $ret;
	}

	function user_add_role($user_id,$role_id_list){
		if (empty($role_id_list)){
			return;
		}
		$user_id = (int)$user_id;
		$role_id_arr = explode(',', $role_id_list);
		$ins_arr = array();
		foreach($role_id_arr as $role_id){
			$ins_arr[] = "({$user_id},{$role_id})";
		}
		$sql = "insert ignore sys_user_role(user_id,role_id) values".join(',',$ins_arr);
		$ret = CTX()->db->query($sql);
		return $ret;
	}
        /**
        * 通过id 查询数据
        */
        function query_by_id($id,$select = '*') {
            $ret = parent :: get_row(array('user_id' => $id));
            return $ret;
        }

    /**
     * 根据店员代码获取信息
     */
    public function get_shop_user($user_code) {
        $sql = 'SELECT su.user_code,su.user_name,bs.shop_name,bs.shop_code FROM sys_user su LEFT JOIN base_shop bs ON su.relation_shop=bs.shop_code WHERE su.type>0 AND su.user_code=:user_code';
        $sql_value = array(':user_code' => $user_code);
        $user = $this->db->get_row($sql, $sql_value);
        return $user;
    }

    function custom_register($user) {
        unset($user['kh_id']);
        $user['password'] = $this->encode_pwd($user['password']);
        $user['create_time'] = date('Y-m-d H:i:s');
        $user['create_person'] = isset($user['create_person']) && !empty($user['create_person']) ? $user['create_person'] : $user['user_code'];
        $user['status'] = isset($user['status']) && !empty($user['status']) ? $user['status'] : 2;//2为 未审核；1启用0未启用
        $user['role_id'] = 100;
        $status = $this->valid($user);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->is_exists($user['user_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) {
            return $this->format_ret('user_error_unique_code');
        }

        $ret_user =  parent::insert($user);
        //维护用户角色关联表信息
        if($ret_user['status']>0){
            $this->create_custom_role($ret_user['data']);
        }


        return $ret_user;
    }
    //添加分销商角色
    function create_custom_role($user_id){
        $sql = "select role_id from sys_role where role_code=:role_code AND sys=12 ";
        $role_id = $this->db->get_value($sql,array(':role_code'=>'distribution'));
        if(!empty($role_id)){
            $role_id_arr = array($role_id);

            $this->add_role($user_id, $role_id_arr);
        }
    }
    //添加分销商角色
    function create_shop_role($user_id){
        $sql = "select role_id from sys_role where role_code=:role_code AND sys=11 ";
        $role_id = $this->db->get_value($sql,array(':role_code'=>'oms_shop'));
        if(!empty($role_id)){
            $role_id_arr = array($role_id);

            $this->add_role($user_id, $role_id_arr);
        }
    }


    function register_search($filter) {
        if(empty($filter['kh_id']) || empty($filter['phone'])){
            return $this->format_ret(-1,'','');
        } else {
            $init_info['client_id'] = $filter['kh_id'];
            CTX()->saas->init_saas_client($init_info);
            $sql_join = "";
            $sql_main = "FROM {$this->table} r $sql_join WHERE 1 AND login_type=2";
            if (isset($filter['phone']) && $filter['phone'] != '') {
                $sql_main .= " AND r.phone=:phone";
                $sql_values[':phone'] = $filter['phone'];
            }

            $select = 'r.*';
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
            foreach ($data['data'] as &$value) {
                $value['status_name'] = $this->status[$value['status']];
            }
            $ret_status = OP_SUCCESS;
            $ret_data = $data;
        }
        return $this->format_ret($ret_status, $ret_data);
    }

    function update_status($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $user_row = $this->get_row(array('user_id' => $id));
        if(empty($user_row['data'])){
            return $this->format_ret(-1,'','查询用户不存在');
        }
        $ret = parent::update(array('status' => $active), array('user_id' => $id));
        return $ret;
    }
    function get_by_code($code,$select = '*') {
        $sql = "SELECT {$select} FROM sys_user WHERE user_code = :user_code";
        return  $this->db->get_row($sql,array(':user_code'=>$code));
    }
    function detail_user_pref($iid) {
        $ret = parent::delete_exp('sys_user_pref', array('iid' => $iid));
        return $ret;
    }
    
    function get_passwod($str){
 
   $str =     base64_encode($str);

    $val_str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    $key_str = strrev($val_str);
    $arr_val =  str_split($val_str);  
    $arr_key =  str_split($key_str);  

    $arr =array_combine($arr_key,$arr_val);
    $str_arr = str_split($str);
    $new_str = '';

    foreach($str_arr as $val){
        $new_str .= $arr[$val];
    }
          //var_dump($str,22,$new_str);
    return  $new_str;
}

 function is_security_role($user_code){
        $sql_u = "select user_id from sys_user where user_code=:user_code ";
        $user_id = $this->db->get_value($sql_u,array(':user_code'=>$user_code));
        $sql_r = "select role_id from sys_role where role_code=:role_code ";
        $role_id = $this->db->get_value($sql_r,array(':role_code'=>'security'));
        $sql = "select user_role_id from sys_user_role where user_id=:user_id AND role_id=:role_id ";
        $user_role_id = $this->db->get_value($sql,array(':user_id'=>$user_id,':role_id'=>$role_id));
        return $user_role_id>0?TRUE:false;

    }
    //返回用户数组
    function sys_user_arr() {
        $sql = " SELECT user_code, user_name from sys_user ";
        $data = $this->db->get_all($sql);
        $user_arr = array();
        foreach ($data as $value) {
            $user_arr[$value['user_code']] = $value['user_name'];
        }
        return $user_arr;
    }

}