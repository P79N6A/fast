<?php
/**
 * 供应商类型相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('base');
require_lib('util/oms_util', true);

class CustomModel extends TbModel {
    private $user_id = 0;
    private $is_manage = -1;
	function get_table() {
		return 'base_custom';
	}

        public $settlement_method = array(
            '0' => '固定运费',
            '1' => '按商品重量计算运费',
//            'real_time_computation' => '实时计算',
//            'ex_post_calculation' => '事后计算',
        );

        public $custom_type = array(
            'pt_fx' => '普通分销',
            'tb_fx' => '淘宝分销',
        );
        public $custom_price_type = array(
            '0' => '吊牌价',/*,array('1','分销价')*/
            '2' => '批发价'
        );

        /*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {
                if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
                    $filter[$filter['keyword_type']] = trim($filter['keyword']);
                }
		$sql_join = "";//LEFT JOIN fx_custom_grades_detail AS r2 ON rl.custom_code = r2.custom_code
                $sql = "SELECT user_code FROM sys_user WHERE status = 2  AND login_type = 2";
                $user_code = $this->db->get_all_col($sql);
		$sql_main = "FROM {$this->table} rl $sql_join WHERE 1 ";
                $sql_values = array();
                if(!empty($user_code)) {
                    $user_str = "'" . implode("','", $user_code) . "'";
                    $user_str = $this->arr_to_in_sql_value($user_code, 'user_code', $sql_values);
                    $sql_main .= " AND (rl.user_code NOT IN ({$user_str}) OR rl.user_code is null) ";
                }
                
                //判断是否是分销产品线过来的
                if(isset($filter['list_type']) && $filter['list_type'] == 'fx_custom_grades') {
                    $sql = "SELECT custom_code FROM fx_custom_grades_detail";
                    $custom_code = $this->db->get_all_col($sql);
                    if(!empty($custom_code)) {
                  
                        $custom_str = $this->arr_to_in_sql_value($custom_code, 'custom_code', $sql_values);
                        $sql_main .= " AND rl.custom_code NOT IN ({$custom_str}) ";
                    }
                }
//                if(empty($filter['list_type']) || (isset($filter['list_type']) && $filter['list_type'] != 'custom_do_list')) {
//                    $filter['is_effective'] = 1;
//                }
	
//		//仓库名称或代码
//		if (isset($filter['code_name']) && $filter['code_name'] != '') {
//			//$sql_main .= " AND (rl.supplier_code LIKE '%" . $filter['code_name'] . "%' or rl.supplier_name LIKE '%" . $filter['code_name'] . "%' )";
//			$sql_main .= " AND (rl.custom_code LIKE :code_name or rl.custom_name LIKE :code_name)";
//			$sql_values[':code_name'] = $filter['code_name'].'%';
//		}
                if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
                    $shop_arr = explode(',', $filter['shop_code']);
                    $sql_shop_values = array();
                    $str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_shop_values);
                    
                    $sql = "SELECT custom_code FROM base_shop WHERE shop_code in ($str)";
                    $custom_code_arr = $this->db->get_all_col($sql,$sql_shop_values);
                  //  $custom_code_arr = "'" . implode("','", $custom_code_arr) . "'";
                }

                if(isset($filter['shop_code']) && $filter['shop_code'] != '' && isset($filter['custom_code']) && $filter['custom_code'] != '') {
                     $str = $this->arr_to_in_sql_value($custom_code_arr, 'custom_code', $sql_values);
                    $sql_main .= " AND (rl.custom_code in ({$str}) OR rl.custom_code LIKE :custom_code) ";
                    $sql_values[':custom_code'] = $filter['custom_code'] . '%';
                } else {
                    //店铺
                    if(isset($filter['shop_code']) && $filter['shop_code'] != '') {
                        $str = $this->arr_to_in_sql_value($custom_code_arr, 'custom_code', $sql_values);
                        $sql_main .= " AND rl.custom_code in ({$str})";
                    } 
                    //分销商编码
                    if(isset($filter['custom_code']) && $filter['custom_code'] != '') {
                        $sql_main .= " AND rl.custom_code LIKE :custom_code";
                        $sql_values[':custom_code'] = $filter['custom_code'] . '%';
                    } 
                }
                if(isset($filter['is_purview']) && $filter['is_purview'] == 1) {
                    $sql_main .= load_model('base/CustomModel')->get_sql_purview_custom('rl.custom_code');
                    $sql_main .= " AND rl.is_effective = 1 ";
                }
                //分销商名称或代码
		if (isset($filter['code_name']) && $filter['code_name'] != '') {
                    $sql_main .= " AND (rl.custom_code LIKE :code_name or rl.custom_name LIKE :code_name)";
                    $sql_values[':code_name'] = '%' . $filter['code_name'].'%';
		}
                //分销商名称
                if(isset($filter['custom_name']) && $filter['custom_name'] != '') {
                    $sql_main .= " AND rl.custom_name LIKE :custom_name";
                    $sql_values[':custom_name'] = '%' . $filter['custom_name'] . '%';
                } 
                //分销商联系人
                if(isset($filter['contact_person']) && $filter['contact_person'] != '') {
                    $sql_main .= " AND rl.contact_person LIKE :contact_person";
                    $sql_values[':contact_person'] = '%' . $filter['contact_person'] . '%';
                } 
                //分销商手机
                if(isset($filter['mobile']) && $filter['mobile'] != '') {
                    $sql_main .= " AND rl.mobile LIKE :mobile";
                    $sql_values[':mobile'] = $filter['mobile'] . '%';
                } 
                //分销商类型
                if(isset($filter['custom_type']) && $filter['custom_type'] != 'all') {
                    $sql_main .= " AND rl.custom_type = :custom_type";
                    $sql_values[':custom_type'] = $filter['custom_type'];
                } 
                //分销商分类
                $custom_grade_arr = array();
                if(isset($filter['custom_grade']) && $filter['custom_grade'] != '') {
                   // $filter['custom_grade'] = deal_strs_with_quote($filter['custom_grade']);
                    $arr = explode(',', $filter['custom_grade']);
                    $sql_val = array();
                    $str = $this->arr_to_in_sql_value($arr, 'grade_code', $sql_val);
                    $sql = "SELECT custom_code FROM fx_custom_grades_detail WHERE grade_code IN ({$str}) ";
                    $custom_grade_arr = $this->db->get_all_col($sql, $sql_val);
                } 
                //启用
		if (isset($filter['is_effective']) && $filter['is_effective'] != 'all') {
                    $sql_main .= " AND rl.is_effective = :is_effective";
                    $sql_values[':is_effective'] = $filter['is_effective'];
		}
                $sql_main .= ' ORDER BY create_time DESC ';

		$select = 'rl.*';

		//$data =  $this->get_page_from_sql($filter, $sql_main, $select);
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
                foreach ($data['data'] as $key => &$value) {
                    if(!empty($custom_grade_arr) && !in_array($value['custom_code'],$custom_grade_arr)) {
                        unset($data['data'][$key]);
                    }
                    $value['yck_account_capital']=$value['yck_account_capital'].'（元）';
                    if(!empty($value['custom_type'])){
                        $value['custom_type_name'] = $this->custom_type[$value['custom_type']];
                    } else {
                        $value['custom_type_name'] = '';
                    }
                    $value['create_date'] = $value['create_time'] != '0000-00-00 00:00:00' ? date("Y-m-d",  strtotime($value['create_time'])) : '';
                    //分销店铺
                    $sql = "SELECT shop_name FROM base_shop WHERE custom_code = :custom_code";
                    $shop_name_arr = $this->db->get_all_col($sql,array(':custom_code' => $value['custom_code']));
                    $value['shop_name_str'] = !empty($shop_name_arr) ? implode(',',$shop_name_arr) : '';
                    //省市区
                    $province = oms_tb_val('base_area', 'name', array('id' => $value['province']));
                    $city = oms_tb_val('base_area', 'name', array('id' => $value['city']));
                    $district = oms_tb_val('base_area', 'name', array('id' => $value['district']));
                    //联系人地址
                    $value['address_str'] = $province . $city . $district .$value['address'];
                    //分销商分类
                    $grade_data = load_model('base/CustomGradesModel')->get_by_code($value['custom_grade'],'grade_name');
                    $value['grade_name'] = $grade_data['grade_name'];
                }

		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);

	}

        //获取有效的分销商
        function get_useful_custom_arr(){
            $sql_val = array();
            $sql = "SELECT user_code FROM sys_user WHERE status != 1 AND login_type = 2";
            $user_code = $this->get_all_col($sql);
            $sql_main = "SELECT rl.* FROM base_custom rl WHERE rl.is_effective = 1 ";
            if(!empty($user_code['data'])) {
                $user_str = $this->arr_to_in_sql_value($user_code['data'],'user_code',$sql_val);
                $sql_main .= " AND (rl.user_code NOT IN ({$user_str}) OR rl.user_code is null) ";
            }
            $data = $this->db->get_all($sql_main, $sql_val);
            return $data;
        }
        
	/**
	 * @param $id
	 * @return array
	 */
	function get_by_id($id) {

		return  $this->get_row(array('custom_id'=>$id));
	}

	/**
	 * @param $code
	 * @return array
	 */
	function get_by_code($code) {
		return $this->get_row(array('custom_code'=>$code));
	}
        /**
	 * @param $name
	 * @return array
	 */
	function get_by_name($name) {
		return $this->get_row(array('custom_name'=>$name));
	}
	/**
	 * 通过field_name查询
	 *
	 * @param  $ :查询field_name
	 * @param  $select ：查询返回字段
	 * @return array (status, data, message)
	 */
	public function get_by_field($field_name,$value, $select = "*") {

		$sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
		$data = $this -> db -> get_row($sql, array(":{$field_name}" => $value));

		if ($data) {
			return $this -> format_ret('1', $data);
		} else {
			return $this -> format_ret('-1', '', 'get_data_fail');
		}
	}
	/*
	 * 添加新纪录
	 */
	function insert($spec1) {
		$status = $this->valid($spec1);
		if ($status < 1) {
			return $this->format_ret($status);
		}

		$ret = $this->is_exists($spec1['custom_code']);

		if (!empty($ret['data'])) {
			return $this->format_ret('-1', '','分销商已存在');
		}
        /*
		$ret = $this->is_exists($spec1['spec1_name'], 'spec1_name');
		if (!empty($ret['data'])) return $this->format_ret(SPEC1_ERROR_UNIQUE_NAME);
		*/
                $spec1['create_time'] = date("Y-m-d H:i:s");
                $this->begin_trans();
                try{
                    //添加分类
                    if(!empty($spec1['custom_grade'])) {
                        $ret = $this->delete_exp('fx_custom_grades_detail', array('custom_code' => $spec1['custom_code']));
                        if ($ret != true) {
                            $this->rollback();
                            return $this->format_ret(-1, '', '添加分销商分类失败');
                        }
                        $data['grade_code'] = $spec1['custom_grade'];
                        $data['data'][] = array(
                            'custom_type' => $spec1['custom_type'],
                            'custom_code' => $spec1['custom_code'],
                            'custom_name' => $spec1['custom_name']
                        );
                        $ret = load_model('base/CustomGradesModel')->insert_custom($data);
                        if($ret['status'] != 1){
                            $this->rollback();
                            return $this->format_ret(-1,'',$ret['message']);
                        }
                    }
                    //创建账号
                    if(!empty($spec1['user_code']) && !empty($spec1['password'])) {
                        $copy_sell_fld = 'user_code,password,address,province,city,district,';
                        $params = load_model('util/ViewUtilModel')->copy_arr_by_fld($spec1, $copy_sell_fld);
                        $params['phone'] = $spec1['mobile'];
                        $params['user_name'] = $spec1['contact_person'];
                        $params['login_type'] = 2;
                        $params['status'] = 1;
                        //创建账号
                        $result = load_model('sys/UserModel')->custom_register($params);
                        if($result['status'] != 1){
                            $this->rollback();
                            return $this->format_ret(-1,'',$result['message']);
                        }
                    }
                    if(!empty($spec1['province'])&&!empty($spec1['contact_person'])){
                        //维护默认地址
                        $data = array(
                            'custom_code' => $spec1['custom_code'],
                            'country' => empty($spec1['country']) ? 1 : $spec1['country'],
                            'province' =>  empty( $spec1['province']) ? 0: $spec1['province'],
                            'city' => empty( $spec1['city']) ? 0: $spec1['city'],
                            'district' => empty($spec1['district']) ? '0' : $spec1['district'],
                            'address' => empty($spec1['address']) ? '' : $spec1['address'],
                            'zipcode' => empty($spec1['zipcode']) ? '' : $spec1['zipcode'],
                            'tel' => empty($spec1['mobile']) ? '' : $spec1['mobile'],
                            'home_tel' => empty($spec1['tel']) ? '' : $spec1['tel'],
                            'is_add_time' => date('Y-m-d H:i:s'),
                            'name' => empty($spec1['contact_person'])?$spec1['contact_person']:'',
                            'is_default' => 1
                        );
                        $ret = $this->insert_exp('base_custom_address', $data);
                        if($ret['status'] != 1){
                            $this->rollback();
                            return $this->format_ret(-1,'',$ret['message']);
                        }
                    }
                    
                    //添加分销商
                    $ret = parent::insert($spec1);
                    if($ret['status'] != 1){
                        $this->rollback();
                        return $this->format_ret(-1,'',$ret['message']);
                    }
                    $this->commit();
                    return $ret;
                } catch (Exception $e) {
                    $this->rollback();
                    return $this->format_ret(-1, '', $e->getMessage());
                }
	}

	/*
	 * 删除记录
	 * */
	function delete($custom_id) {
		$used = $this->is_used_by_id($custom_id);
		if($used){
			return $this->format_ret(-1,array(),'已经在业务系统中使用，不能删除！');
		}
                $custom_data = $this->get_by_id($custom_id);
                $this->begin_trans();
                if(!empty($custom_data['data']['user_code'])) {
                    $user_model = load_model('sys/UserModel');
                    //账号信息
                    $user_data = $user_model->get_by_code($custom_data['data']['user_code']);
                    //删除对应的账号
                    $ret = load_model('sys/UserModel')->delete($user_data['user_id']);
                    if($ret['status'] < 0) {
                        $this->rollback();
                        return $ret;
                    }
                }
                //删除关联的多地址
                $this->delete_exp('base_custom_address', array('custom_code'=>$custom_data['data']['custom_code']));
                if($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                //删除关联的多地址日志
                $this->delete_exp('base_custom_address_log', array('custom_code'=>$custom_data['data']['custom_code']));
                if($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                
		$ret = parent::delete(array('custom_id'=>$custom_id));
                if($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                $this->commit();
		return $ret;
	}

	/*
	 * 修改纪录
	 */
	function update($spec1, $custom_id) {
            $status = $this->valid($spec1, true);
            if ($status < 1) {
                return $this->format_ret($status);
            }
            if ($spec1['custom_rebate'] > 1 || $spec1['custom_rebate'] < 0 ) {
                return $this->format_ret(-1,'','折扣值必须大于0小于等于1!');
            }
            $custom_data = $this->get_by_id($custom_id);
            $this->begin_trans();
            try {
                //添加分类
                if (!empty($spec1['custom_grade'])) {
                    $ret = $this->delete_exp('fx_custom_grades_detail', array('custom_code' => $custom_data['data']['custom_code']));                    
                    if ($ret != true) {
                        $this->rollback();
                        return $this->format_ret(-1, '', '添加分销商分类失败');
                    }
                    $data['grade_code'] = $spec1['custom_grade'];
                    $data['data'][] = array(
                        'custom_type' => $spec1['custom_type'],
                        'custom_code' => $custom_data['data']['custom_code'],
                        'custom_name' => $spec1['custom_name']
                    );
                    $ret = load_model('base/CustomGradesModel')->insert_custom($data);
                    if ($ret['status'] != 1) {
                        $this->rollback();
                        return $this->format_ret(-1, '', $ret['message']);
                    }
                } else {
                    $ret = $this->delete_exp('fx_custom_grades_detail', array('custom_code' => $custom_data['data']['custom_code']));
                    if ($ret != true) {
                        $this->rollback();
                        return $this->format_ret(-1, '', '删除分销商分类失败');
                    }
                }
                //创建账号
                if (!empty($spec1['user_code']) && !empty($spec1['password']) && empty($custom_data['data']['user_code'])) {
                    $copy_sell_fld = 'user_code,password,address,province,city,district,';
                    $params = load_model('util/ViewUtilModel')->copy_arr_by_fld($spec1, $copy_sell_fld);
                    $params['phone'] = $spec1['mobile'];
                    $user_code = CTX()->get_session('user_code');
                    $params['create_person'] = $user_code;
                    $params['user_name'] = $spec1['contact_person'];
                    $params['login_type'] = 2;
                    $params['status'] = 1;
                    //创建账号
                    $result = load_model('sys/UserModel')->custom_register($params);
                    if ($result['status'] != 1) {
                        $this->rollback();
                        return $this->format_ret(-1, '', $result['message']);
                    }
                }
                //回写多地址表
                $data = array(
                    'country' => $spec1['country'],
                    'province' => $spec1['province'],
                    'city' => $spec1['city'],
                    'district' => $spec1['district'],
                    'address' => $spec1['address'],
                    'tel' => $spec1['mobile'],
                    'home_tel' => $spec1['tel'],
                    'name' => $spec1['contact_person']
                );
                $where = array(
                    'custom_code' => $custom_data['data']['custom_code'],
                    'is_default' => 1
                );
                $ret = $this->update_exp('base_custom_address', $data, $where);
                if($ret['status'] != 1){
                    $this->rollback();
                    return $this->format_ret(-1,'',$ret['message']);
                }
                
                $ret = parent::update($spec1, array('custom_id' => $custom_id));
                if($ret['status'] != 1){
                    $this->rollback();
                    return $this->format_ret(-1,'',$ret['message']);
                }
                $this->commit();
                return $ret;
            } catch (Exception $e) {
                $this->rollback();
                return $this->format_ret(-1, '', $e->getMessage());
            }
        }

        function update_custom_by_code($arr,$where){
            return parent::update($arr, $where);
        }

	/*
	 * 服务器端验证
	 */
	private function valid($data, $is_edit = false) {
		if (!$is_edit && (!isset($data['custom_code']) || !valid_input($data['custom_code'], 'required'))) return 'SUPPLIER_ERROR_CODE';
		if (!isset($data['custom_name']) || !valid_input($data['custom_name'], 'required')) return 'SUPPLIER_ERROR_NAME';

		return 1;
	}

	function is_exists($value, $field_name='custom_code') {
		$ret = parent::get_row(array($field_name=>$value));

		return $ret;
	}
	/**
	 * 生成单据号
	 */
	function create_fast_bill_sn()
	{
		$sql = "select custom_id  from {$this->table}   order by custom_id desc limit 1 ";
		$data = $this->db->get_all($sql);
		if ($data) {
			$djh = intval($data[0]['custom_id'])+1;
		} else {
			$djh = 1;
		}
		require_lib ( 'comm_util', true );
		$jdh = "FXXBH" . add_zero($djh,3);
		return $jdh;
	}
	/**
	 * 根据id判断在业务系统是否使用
	 * @param int $id
	 * @return boolean 已使用返回true, 未使用返回false
	 */
	public function is_used_by_id($id) {
		$result = $this->get_value("select custom_code from {$this->table} where custom_id=:id", array(':id' => $id));
		$code = $result['data'];
		$num_return = $this->get_num('select * from wbm_return_record where distributor_code=:code', array(':code' => $code));
		$num_store_shift = $this->get_num('select * from wbm_store_out_record where distributor_code=:code', array(':code' => $code));
                $record_num = $this->db->get_value('SELECT count(*) FROM oms_sell_record WHERE fenxiao_code = :custom_code',array(':custom_code' => $code));
                $return_num = $this->db->get_value('SELECT count(*) FROM oms_sell_return WHERE fenxiao_code = :custom_code',array(':custom_code' => $code));
		if((isset($num_return['data'])&& $num_return['data']>0) || (isset($num_store_shift['data'])&& $num_store_shift['data']>0) || $record_num > 0 || $return_num > 0){
			//已经在业务系统使用
			return true;
		}else{
			//尚未在业务系统使用
			return false;
		}
	}
        //获取所有的运费结算方式
        public function get_s1ettlement_method(){
            $settlement_method = $this->settlement_method;
            $new_settlement_method = array();
            $i = 0;
            foreach ($settlement_method as $key => $method){
                $new_settlement_method[$i]['settlement_method_code'] = $key;
                $new_settlement_method[$i]['settlement_method_name'] = $method;
                $i ++;
            }
            return $new_settlement_method;
        }

        public function get_custom_type(){
            $custom_type = $this->custom_type;
            $new_custom_type = array();
            $i = 0;
            foreach ($custom_type as $key => $type){
                $new_custom_type[$i]['settlement_method_code'] = $key;
                $new_custom_type[$i]['settlement_method_name'] = $type;
                $i ++;
            }
            return $new_custom_type;
        }

        public function get_custom_by_custom_type($custom_type){
            $sql = "select custom_code,custom_name from {$this->table} where custom_type = :custom_type ";
            $custom = $this->db->get_all($sql,array(":custom_type" => $custom_type));
            $custom_arr = array();
            if(!empty($custom)){
                foreach ($custom as $value){
                    $custom_arr[] = $value['custom_code'];
                }
            }
            return $custom_arr;
        }

        public function set_login_user($user){
            $this->begin_trans();
            try {
                $custom_code = $user['custom_code'];
                if(empty($custom_code)){
                    return $this->format_ret(-1,'','编辑失败，分销商为空');
                }
                unset($user['custom_code']);
                $set_user_ret = parent::update(array('user_code' => $user['user_code']), array('custom_code' => $custom_code));
                if($set_user_ret['status'] < 0){
                    $this->rollback();
                    return $this->format_ret(-1,'','设置用户名失败');
                }
                $ret = load_model('sys/UserModel')->insert_user($user);

                if($ret['status'] < 0 || $ret['status'] == 'user_error_unique_code'){
                    $this->rollback();
                    return $this->format_ret(-1,'',$ret['message']);
                }
                $this->commit();
                return $this->format_ret(1);
            } catch (Exception $e) {
                $this->rollback();
		return $this->format_ret(-1, '', $e->getMessage());
            }


        }

        function reset_pwd($data){
            if(empty($data['user_code'])){
                return $this->format_ret(-1,'','用户登录名为空！');
            }
            $user_id = $this->db->get_value("select user_id from sys_user where user_code = :user_code",array(':user_code' => $data['user_code']));
            $ret = load_model('sys/UserModel')->reset_pwd($user_id);
            return $ret;
        }

        function get_shop_info($data){
            if(!empty($data['kh_id'])){
                $init_info['client_id'] = $data['kh_id'];
                CTX()->saas->init_saas_client($init_info);
                		$this->db = &CTX()->db;
                $sql = "select value from sys_auth where code = 'company_name'";
                $row = $this->db->get_row($sql);
                if(!empty($row['value'])){
                    return $this->format_ret(1,$row['value'],'');
                }
            }
            return $this->format_ret(-1,'','您申请的分销信息不存在');

        }

        //应用于分销商审核列表
    function get_review_list($filter) {
        $sql_join = " LEFT JOIN base_custom r2 ON rl.user_code = r2.user_code ";
        $sql_main = "FROM sys_user rl $sql_join WHERE 1";
        $sql_values = array();
        $sql_main .= " AND login_type = 2 AND status = 2";
        if (isset($filter['phone']) && $filter['phone'] != '') {
            $sql_main .= " AND rl.phone = :phone ";
            $sql_values[':phone'] = $filter['phone'];
        }
        $select = 'rl.user_code, rl.user_name,rl.create_time,rl.phone,rl.user_id,r2.custom_name';

        //$data =  $this->get_page_from_sql($filter, $sql_main, $select);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            if (!empty($value['custom_type'])) {
                $value['custom_type_name'] = $this->custom_type[$value['custom_type']];
            } else {
                $value['custom_type_name'] = '';
            }
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }


    function custom_register($user){
        $captcha_code = strtolower(CTX()->get_session('captcha_code'));
        if(strtolower($user['captcha']) != $captcha_code) {
            return $this->format_ret(-1,'','验证码不正确');
        }
        if(empty($user['user_code']) || empty($user['password']) || empty($user['phone']) || empty($user['user_name'])){
            return $this->format_ret(-1,'','登录名、密码、姓名、手机不能为空');
        }
        $this->begin_trans();
        try {
            $result = load_model('sys/UserModel')->custom_register($user);
            if($result['status'] != 1){
                $this->rollback();
                return $this->format_ret(-1,'',$result['message']);
            }
            $params = array();
            $params['company_name'] = empty($user['company_name']) ? '':$user['company_name'];
            $params['address'] = empty($user['address']) ? '':$user['address'];
            $params['custom_code'] = $user['user_code'];
            if(empty($params['company_name'])) {
                $params['custom_name'] = $user['user_name'];
            } else {
                $params['custom_name'] = $params['company_name'];
            }
            $params['contact_person'] = $user['user_name'];
            $params['custom_type'] = 'pt_fx';
            $params['user_code'] = $user['user_code'];
            $params['mobile'] = $user['phone'];
            $params['create_time'] = date('Y-m-d H:i:s');
            $params['country'] = 1;
            $params['province'] = $user['province'];
            $params['city'] = $user['city'];
            $params['district'] = $user['district'];
            $ret = $this->insert($params);
            $aff = $this->affected_rows();
            if($aff != 1) {
                return $this->format_ret(-1,'','注册失败');
            }
            if($ret['status'] != 1){
                $this->rollback();
                return $ret;
            }
            $this->commit();
            return $this->format_ret(1,'','注册成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

        function get_custom_by_user_code($user_code,$select = 'custom_code,custom_name'){
        $sql = "select {$select} from base_custom where user_code = :user_code";
        $custom = $this->db->get_row($sql,array(":user_code" => $user_code));
        return $custom;
    }

    /**
     * @todo 获取所有分销商代码和名称
     */
    function get_custom_info() {
        $sql = "SELECT custom_code,custom_name FROM {$this->table}";
        $custom = $this->db->get_all($sql);
        $custom_arr = array();
        $i = 0;
        if (!empty($custom)) {
            foreach ($custom as $value) {
                $custom_arr[$i]['custom_code'] =  $value['custom_code'];
                $custom_arr[$i]['custom_name'] =  $value['custom_name'];
                $i++;
            }
        }
        return $custom_arr;
    }
    /**
     * @todo 获取没有绑定淘宝店铺的分销商代码和名称
     */
    function get_custom_no_shop_info() {
        $sql = "SELECT custom_code,custom_name FROM {$this->table} WHERE custom_type = 'pt_fx' AND is_effective = 1";
        $custom = $this->db->get_all($sql);
        $custom_arr = array();
        $i = 0;
        if (!empty($custom)) {
            foreach ($custom as $value) {
                $custom_arr[$i]['custom_code'] =  $value['custom_code'];
                $custom_arr[$i]['custom_name'] =  $value['custom_name'];
                $i++;
            }
        }
        $data = array_merge(array(array('', '请选择')), $custom_arr);
        return $data;
    }
    // 获取分销商信息
    function get_session_data($data) {
        if (CTX()->is_in_cli()) {
            $user_code = load_model('sys/UserTaskModel')->get_user_code();
            if($data == 'login_type') {
                $sql_user = "select login_type from sys_user where user_code=:user_code";
                $sql_values = array(':user_code' => $user_code);
                $return_data = $this->db->get_value($sql_user, $sql_values);
            } else if($data == 'user_code') {
                $return_data = $user_code;
            }
        } else {
            if($data == 'login_type') {
                $return_data = CTX()->get_session('login_type');
            } else if($data == 'user_code') {
                $return_data = CTX()->get_session('user_code');
            }
        }
        return $return_data;
    }

    //传入一个二维数组，将它排序,升序排序
    function array_order($arr,$type){
        //转换编码构造
        foreach ($arr as $key => $item) {
            $sort_array[] = iconv("UTF-8", "GB2312", $item[$type]);
        }
        //调用函数，注意参数相关写法
        array_multisort($sort_array, SORT_STRING, $arr);
        return $arr;
    }
    //修改分销商金额
    function update_money($capital_data) {
        if ($capital_data['capital_type'] == 0) {
            if ($capital_data['capital_account'] == 'yck') {
                //修改分销商的预存款账户余额
                $sql = "UPDATE base_custom SET yck_account_capital = yck_account_capital - {$capital_data['money']} WHERE custom_code = '{$capital_data['distributor']}'";
            }
        } else if($capital_data['capital_type'] == 1){
            if ($capital_data['capital_account'] == 'yck') {
                //修改分销商的预存款账户余额
                $sql = "UPDATE base_custom SET yck_account_capital = yck_account_capital + {$capital_data['money']} WHERE custom_code = '{$capital_data['distributor']}'";
            }
        }
        $this->query($sql);
        $update_num = $this->affected_rows();
        return $update_num;
    }
    //启用、停用
    function is_effective($custom_id,$is_effective) {
        $custom_data = $this->get_by_id($custom_id);
        if(empty($custom_data)) {
            return $this->format_ret(-1,'','分销商不存在');
        }
        //查询分销店铺是否全部停用
        $shop_data = load_model('base/ShopModel')->get_by_custom_code($custom_data['data']['custom_code']);
        if(!empty($shop_data[0]['custom_code']) && $is_effective == 0) {
            return $this->format_ret(-1,'','请先停用绑定的店铺');
        }
        //判断分销商是否有账号user_code
        if(!empty($custom_data['data']['user_code'])){
            //同步更新用户表中的用户状态
            $up_data = array();
            $up_data['status'] = $is_effective;
            $this->update_exp('sys_user', $up_data, array('user_code' => $custom_data['data']['user_code']));
        }
        return parent::update(array('is_effective' => $is_effective), array('custom_id' => $custom_id));
    }
    /**
     * 根据当前账号查询分销商信息
     * return array()
     */
    function get_custom_data($user_code) {
        //分销商信息
        $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code, '*');
        //省市区
        $province = oms_tb_val('base_area', 'name', array('id' => $custom['province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $custom['city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $custom['district']));
        //联系人地址
        $custom['address_str'] = $province . $city . $district;
        //类型
        $custom['custom_type_name'] = $this->custom_type[$custom['custom_type']];
        //分类
        $custom['custom_grade'] = oms_tb_val('fx_custom_grades', 'grade_name', array('grade_code' => $custom['custom_grade']));
        //结算方式
        $custom['settlement_method'] = $this->settlement_method[(int)$custom['settlement_method']];
        //结算价格
        $custom['custom_price_type'] = $this->custom_price_type[(int)$custom['custom_price_type']];;
        return $custom;
    }
    //分销商修改信息
    function custom_do_edit($data,$custom_id) {
        $custom = $this->get_by_id($custom_id);
        if($custom['data']['is_effective'] == 0) {
            return $this->format_ret(-1,'','该分销商已停用');
        }
        $ret = parent::update($data, array('custom_id' => $custom_id));
        return $ret;
    }
    //更新欠款
    function update_arrears($custom_code,$arrears_money) {
        $ret = parent::update(array('arrears_money' => $arrears_money), array('custom_code' => $custom_code));
        return $ret;
    }
    function get_custom_select($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} bs where is_effective = 1  ";
        $sql_main .= $this->get_sql_purview_custom('bs.custom_code');
        
        $sql_user = "SELECT user_code FROM sys_user WHERE (status = 2 OR status = 0) AND login_type = 2;";
        $user_code = $this->db->get_all_col($sql_user);
        if (!empty($user_code)) {
            $user_str = "'" . implode("','", $user_code) . "'";
            $sql_main .= " AND (bs.user_code NOT IN ({$user_str}) OR bs.user_code is null) ";
        }

        if (isset($filter['custom_name']) && $filter['custom_name'] != '') {
            $sql_main .= " AND (bs.custom_name LIKE :custom_name or bs.custom_code LIKE :custom_name)";
            $sql_values[':custom_name'] = "%{$filter['custom_name']}%";
        }
        if (isset($filter['custom_type']) && $filter['custom_type'] != '') {
            $sql_main .= " AND bs.custom_type = :custom_type";
            $sql_values[':custom_type'] = "{$filter['custom_type']}";
        }

        $select = 'bs.custom_name,bs.custom_code,bs.custom_type';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    function get_purview_custom_select($custom_type = '',$type = 0) {        
        $data = $this->get_purview_custom('yes',$custom_type);
        if($type == 1) {
           $data = array_merge(array(array('', '全选')), $data);
        } else if($type == 2) {
           $data = array_merge(array(array('', '请选择')), $data);
        } else if ($type == 3) {
            $selete_arr = array();
            foreach($data as $val) {
                $selete_arr[] = array('value' => $val['custom_code'],'text' => $val['custom_name']);
            }
           $data = json_encode($selete_arr);
        } else if($type == 4) {
           $data = $this->array_order($data,'custom_name');
        } else if($type == 5) {
            $arr = array();
            $arr[''] = '请选择';
            foreach ($data as $val) {
                $arr[$val['custom_code']] = $val['custom_name'];
            }
            $data = $arr;
        }
        return $data;
    }
    /**
     * 取出有权限/已启用的分销商
     */
    function get_purview_custom($is_effective = 'no', $custom_type = '', $fld = 'custom_code,custom_name') {
        $this->set_user_manage();

        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('custom_power'));
        $custom_power = $ret_cfg['custom_power'];

        $sql = "select $fld FROM {$this->table} t where 1 ";
        $sql_user = "SELECT user_code FROM sys_user WHERE (status = 2 OR status = 0) AND login_type = 2;";
        $user_code = $this->db->get_all_col($sql_user);
        if (!empty($user_code)) {
            $user_str = "'" . implode("','", $user_code) . "'";
            $sql .= " AND (t.user_code NOT IN ({$user_str}) OR t.user_code is null) ";
        }
        if($is_effective == 'yes') {
            $sql .= " AND is_effective = 1 ";
        }
        if(!empty($custom_type)) {
            $sql .= " AND custom_type = '{$custom_type}' ";
        }
        $version_no = load_model('sys/SysAuthModel')->product_version_no();
        if ((int) $this->is_manage == 0 && $custom_power == 1 && $version_no > 0) {
            $supplier_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id, 7);
            $supplier_code = '';
            if (!empty($supplier_code_arr)) {
                $supplier_code = deal_array_with_quote($supplier_code_arr);
            }

            if (empty($supplier_code)) {
                return array();
            } else {
                $sql .=" and custom_code in ({$supplier_code})";
            }
        }

        $rs = $this->db->get_all($sql);
        return $rs;
    }
    /*
     * 获取用户信息
     */
    function set_user_manage() {
        if ($this->is_manage < 0) {
            $this->user_id = CTX()->get_session('user_id');
            if (empty($this->user_id)) {
                $user_code = load_model('sys/UserTaskModel')->get_user_code();
                $sql_user = "select user_id,is_manage from sys_user where user_code=:user_code";
                $sql_values = array(':user_code' => $user_code);
                $user_row = $this->db->get_row($sql_user, $sql_values);
                $this->user_id = $user_row['user_id'];
            }
            $this->is_manage = 0;
            $sql_role = "select r.role_code from  sys_role r
                    INNER JOIN sys_user_role u ON r.role_id=u.role_id
                    where r.role_code='manage' AND u.user_id=:user_id ";
            $sql_values2 = array(':user_id' => $this->user_id);
            $role_row = $this->db->get_row($sql_role, $sql_values2);
            if (!empty($role_row)) {
                $this->is_manage = 1;
            }
        }
    }
    /**
     * 取出有权限的分销商 拼装SQL时用
     * $fld sql字段名 多表查的要传参如r1.supplier_code ,
     * $req_code 客户端传来的supplier_code（要去掉客户端传来没权限的supplier_code）
     * @return array()
     */
    function get_sql_purview_custom($fld = 'custom_code', $req_code = null) {
        $this->set_user_manage();
        
        if ((int) $this->is_manage == 1 && empty($req_code)) {
            return '';
        }

        $ret = $this->get_purview_custom();

        $req_custom_code_arr = array();
        if (!empty($req_code)) {
            $req_custom_code_arr = explode(',', $req_code);
        }

        $custom_code_arr = array();
        foreach ($ret as $sub_ret) {
            $custom_code_arr[] = $sub_ret['custom_code'];
        }
        if (empty($custom_code_arr)) {
            $str = " and 1!=1 ";
        } else {
            if (!empty($req_custom_code_arr)) {
                $custom_code_arr = array_intersect($custom_code_arr, $req_custom_code_arr);
            }
            if (empty($custom_code_arr)) {
                $str = " and 1!=1 ";
            } else {
                $str = ' and ' . $fld . ' in (\'' . join("','", $custom_code_arr) . '\')';
            }
        }
        return $str;
    }
    /*
     * * 方法名       api_get_custome                       
     *
     * 功能描述     获取分销商信息
     *
     * @author      F.ling
     * @date        2017.03.01
     * @param       $param
     *              array(
     *                  可选: 'custom_type', 'start_time','end_time','start_lastchanged','end_lastchanged'
     *                  'page','page_size'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     *
     */
    function api_get_custom($param){
        //可选字段说明
        $key_option = array(
            's'=>array('custom_type','start_time','end_time','start_lastchanged','end_lastchanged'),
            'i'=>array('page','page_size'),
        );
        //提取可选字段已赋值部分
        $arr_option = array();
        //提取可选字段中已赋值数据
        valid_assign_array($param, $key_option, $arr_option);
        //合并数据
        $arr_deal = $arr_option;
            if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
                    return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
            }
            //清空无用数据
            unset($arr_option);
            unset($param);
            $select = 'custom_code,custom_name,custom_type,contact_person,mobile,tel,province,city,district,address,create_time,lastchanged';
            $sql_values = array();
            $sql_main = "FROM {$this->table} where 1";
            foreach ($arr_deal as $key => $val) {
                if ($key != 'page' && $key != 'page_size') {
                    if($key == 'custom_type'){
                        $sql_values[":{$key}"] = $val;
                        $sql_main .= " AND custom_type=:{$key}";
                    }
                    if($key == 'start_time'){
                        $sql_values[":{$key}"] = $val;
                        $sql_main .= " AND create_time >=:{$key}";
                    }
                    if($key == 'end_time'){
                        $sql_values[":{$key}"] = $val;
                        $sql_main .= " AND create_time <=:{$key}";
                    }
                    if($key == 'start_lastchanged'){
                        $sql_values[":{$key}"] = $val;
                        $sql_main .= " AND lastchanged >=:{$key}";
                    }
                    if($key == 'end_lastchanged'){
                        $sql_values[":{$key}"] = $val;
                        $sql_main .= " AND lastchanged <=:{$key}";
                    }
                }
            }
            $sql_main .= ' group by custom_id ';
            $ret = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select,true);
            foreach($ret['data'] as $key=>&$v){
                        $province = load_model('base/TaobaoAreaModel')->get_by_field('id',$v['province'],'name');
                        $ret['data'][$key]['province'] = $province['data']['name'];
                        $city =  load_model('base/TaobaoAreaModel')->get_by_field('id',$v['city'],'name');
                        $ret['data'][$key]['city'] = $city['data']['name'];
                        $district = load_model('base/TaobaoAreaModel')->get_by_field('id',$v['district'],'name');
                        $ret['data'][$key]['district'] = $district['data']['name'];
            }
            $ret_status = OP_SUCCESS;
            return $this -> format_ret($ret_status, $ret);
    }
    //查询分销商所有快递运费
    function get_by_custom_express_data($custom_code) {
        $sql = "SELECT * FROM base_custom_express_freight WHERE custom_code = :custom_code ";
        $ret = $this->db->get_all($sql, array(':custom_code' => $custom_code));
        return $ret;
    }
    //查询单个分销商运费
    function get_custom_express_row($custom_code, $express_code) {
        $sql = "SELECT * FROM base_custom_express_freight WHERE custom_code = :custom_code AND express_code = :express_code ";
        $ret = $this->db->get_row($sql, array(':custom_code' => $custom_code, ':express_code' => $express_code));
        return $ret;
    }
    
    //保存分销商快递运费
    function save_express_money($express_data, $custom_code) {
        if(empty($express_data)) {
            $ret = $this->delete_exp('base_custom_express_freight', array("custom_code" => $custom_code));
            if($ret == true) {
                return $this->format_ret(1, '', '操作成功');  
            }
        }
        $params = array();
        foreach ($express_data as $val) {
            $params[] = array(
                'custom_code' => $custom_code,
                'express_code' => $val['express_code'],
                'express_money' => $val['express_money'],
            );
        }
        $update_str = "express_money=VALUES(express_money)";
        $this->begin_trans();
        $ret = $this->delete_exp('base_custom_express_freight', array("custom_code" => $custom_code));
        if($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        
        $ret = $this->insert_multi_duplicate('base_custom_express_freight', $params, $update_str);
        if($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        return $ret;
    }
    
    /**
     * @todo 获取所有分销商代码和名称
     */
    function get_custom_arr() {
        $sql = "SELECT custom_code,custom_name FROM {$this->table}";
        $custom = $this->db->get_all($sql);
        $custom_arr = array();
        if (!empty($custom)) {
            foreach ($custom as $value) {
                $custom_arr[$value['custom_code']] =  $value['custom_name'];
            }
        }
        return $custom_arr;
    }
}
