<?php
/**
 * 规格1相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('prm');
require_lib('util/oms_util', true);
class BrandModel extends TbModel {
    private $user_id = 0;
    private  $is_manage = -1;
	function get_table() {
		return 'base_brand';
	}

	/*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {

		$sql_join = "";
		$sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
		$sql_values = array();
		//名称或代码
		if (isset($filter['code_name']) && $filter['code_name'] != '') {
			//$sql_main .= " AND (rl.store_code LIKE '%" . $filter['code_name'] . "%' or rl.store_name LIKE '%" . $filter['code_name'] . "%' )";
			$sql_main .= " AND (rl.brand_code LIKE :code_name or rl.brand_name LIKE :code_name)";
			$sql_values[':code_name'] = $filter['code_name'].'%';
		}
        //对外接口 增量下载时间
        if (isset($filter['lastchanged']) && $filter['lastchanged'] !== '') {
            $sql_main .= " AND rl.lastchanged > :lastchanged ";
            $sql_values[':lastchanged'] =  $filter['lastchanged'] ;
        }
        if(isset($filter['is_api']) && $filter['is_api'] !== ''){
            $sql_main .= " order by rl.lastchanged desc ";
        }

		$select = 'rl.*';
		//$data =  $this->get_page_from_sql($filter, $sql_main, $select);
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		//$url = 'http://'.$_SERVER['HTTP_HOST'].'/webapp/uploads/';
        /**
		foreach ($data['data'] as $key => $value){
			if($value['brand_logo'] != ''){
				$logo_arr = explode('|',$value['brand_logo']);
				$logo = isset($logo_arr[0])?$logo_arr[0]:'';
				$data['data'][$key]['brand_logo'] = "<span><img src='".$url.'/'.$logo."'/ width=50 height=50></span>";
			}
		}**/
		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);
	}

	function get_by_id($id) {

		return  $this->get_row(array('brand_id'=>$id));
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
	//品牌list
	 function get_brand(){
		$sql = "select brand_id,brand_code,brand_name FROM {$this->table} ";
		$rs = $this->db->get_all($sql);
		return $rs;
	}
        //品牌名称list
	 function get_brand_name(){
            $sql = "select brand_id,brand_code,brand_name FROM {$this->table} order by CONVERT(brand_name USING gbk);";
            $data = $this->db->get_all($sql);
            $arr = array();
            foreach($data as $val){
                $arr[] = array($val['brand_code'],$val['brand_name']);
            }
            return $arr;
        }
	
        
	/*
	 * 添加新纪录
	 */
	function insert($brand) {
		$status = $this->valid($brand);
		if ($status < 1) {
			return $this->format_ret($status);
		}

		$ret = $this->is_exists($brand['brand_code']);

		if (!empty($ret['data'])) return $this->format_ret(BRAND_ERROR_UNIQUE_CODE);

		return parent::insert($brand);
	}

	/*
	 * 删除记录
	 * */
	function delete($brand_id) {
            $data = oms_tb_val('base_brand', 'brand_id', array('brand_id'=>$brand_id));
            if (isset($data) && $data == '') {
                return $this->format_ret(-1,array(),'品牌数据已删除！');
            }
            $used = $this->is_used_by_id($brand_id);
            if($used['status']==1){
                return $this->format_ret(-1,$used['data'],'已经在业务系统中使用，不能删除！');
            }
            $ret = parent::delete(array('brand_id'=>$brand_id));
            return $ret;
	}

	/*
	 * 修改纪录
	 */
	function update($brand, $brand_id) {
		$status = $this->valid($brand, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		$ret2 = $this->get_row(array('brand_id'=>$brand_id));
		if(isset($brand['brand_code']) &&  $brand['brand_code'] != $ret2['data']['brand_code']){
			$ret1 = $this->is_exists($brand['brand_code'], 'brand_code');
			if (!empty($ret1['data'])) return $this->format_ret(BRAND_ERROR_UNIQUE_CODE);
		}

		$ret = parent::update($brand, array('brand_id'=>$brand_id));

                $data = array(
                   // 'brand_code'=>$brand['brand_code'],
                    'brand_name'=>$brand['brand_name']
                );
                load_model('prm/GoodsModel')->update_property($data, array('brand_code'=>$ret2['data']['brand_code']) );


		return $ret;
	}



	/*
	 * 服务器端验证
	 */
	private function valid($data, $is_edit = false) {
		if (!$is_edit && (!isset($data['brand_code']) || !valid_input($data['brand_code'], 'required'))) return BRAND_ERROR_CODE;
		if (!isset($data['brand_name']) || !valid_input($data['brand_name'], 'required')) return BRAND_ERROR_NAME;

		return 1;
	}

	function is_exists($value, $field_name='brand_code') {
		$ret = parent::get_row(array($field_name=>$value));

		return $ret;
	}


    /**
     * 返回code=>name数组, 默认仅返回启用的仓库
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-06
     * @param   array   $filter  过滤条件, 如array('status'=>'1')
     * @return  array
     */
    public function get_code_name($filter=array('status'=>1)){
        $result = $this->get_purview_brand();
        $return = array();
        if(is_array($result)) {
            foreach ($result as $value) {
                $return[$value['brand_code']] = $value['brand_name'];
            }
        }
        return $return;
    }

    /**
     * 根据id判断在业务系统是否使用
     * @param int $id
     * @return boolean 已使用返回true, 未使用返回false
     */
    public function is_used_by_id($id) {
        $result = $this->get_value('select brand_code from base_brand where brand_id=:id', array(':id' => $id));
        $code = $result['data'];
        $num = $this->get_num('select * from base_goods where brand_code=:code', array(':code' => $code));
        if(isset($num['data'])&&$num['data']>0){
            //已经在业务系统使用
            return $this->format_ret(1, $code, '');
        }else{
            //尚未在业务系统使用
            return false;
        }
    }

    /**
     * 取出有权限的品牌
     * @return array()
     */
    function get_purview_brand($fld='brand_code,brand_name') {
        $this->set_user_manage();
        //echo '<hr/>$order_info<xmp>'.var_export($_SESSION,true).'</xmp>';
        $sql = "select $fld FROM {$this->table} t where 1 ";
        //print_r(CTX());
        //1标准，2企业，3旗舰
        $version = load_model('sys/SysAuthModel')->product_version_no();
        $login_type = CTX()->get_session('login_type'); //只有系统用户做权限控制，门店不需要
        if( ($version == '1' || $version == '2'  ) && ($this->is_manage == 0) && $login_type < 1){
                $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('brand_power'));
                $brand_power = $ret_cfg['brand_power'];
                if($brand_power==1){
                    if (CTX()->is_in_cli()) {
                        $brand_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id,3);
                        $brand_code = '';
                        if(!empty($brand_code_arr)){
                            $brand_code = implode(",", $brand_code_arr);
                        }
                    } else {
                        $brand_code = CTX()->get_session('brand_code');
                    }

                    if (empty($brand_code)) {
                        return array();
                    } else {
                        $brand_code = str_replace(",", "','", $brand_code);
                        $sql .=" and brand_code in ('" . $brand_code . "')";
                    }
               }
        }
        $sql .= " order by CONVERT(brand_name USING gbk)";
        $rs = $this->db->get_all($sql);
        return $rs;
    }
    //下拉选择框
    function get_purview_brand_select() {
            //品牌  start
            $arr_brand = $this->get_purview_brand();
            $key = 0;
            foreach ($arr_brand as $value) {
                $arr_brand[$key][0] = $value['brand_code'];
                $arr_brand[$key][1] = $value['brand_name'];
                $key++;
            }
            return $arr_brand;
        }

     /**
     * 取出有权限的品牌 拼装SQL时用
     * $fld sql字段名 多表查的要传参如r1.brand_code ,
     * $req_code 客户端传来的brand_code（要去掉客户端传来没权限的brand_code）
     * @return array()
     */
	function get_sql_purview_brand($fld = 'brand_code',$req_code = null){
            $this->set_user_manage();
                $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('brand_power'));
                $brand_power = $ret_cfg['brand_power'];
                if($brand_power==0){
                    return '';
                }

		if($this->is_manage == 1 && empty($req_code)){
			return '';
		}
		$req_brand_code_arr = array();
		if (!empty($req_code)){
			$req_brand_code_arr = explode(',',$req_code);
		}
		$ret = $this->get_purview_brand();
		$brand_code_arr = array();
		foreach($ret as $sub_ret){
			$brand_code_arr[] = $sub_ret['brand_code'];
		}
		if (empty($brand_code_arr)){
			$str = " and 1!=1 ";
		}else{
		if (!empty($req_brand_code_arr)){
			$brand_code_arr = array_intersect($brand_code_arr,$req_brand_code_arr);
		}
		if (empty($brand_code_arr)){
			$str = " and 1!=1 ";
		}else{
			$str = ' and '.$fld.' in (\''.join("','",$brand_code_arr).'\')';
		}
		}
		return $str;
	}


    function set_user_manage() {
        if ($this->is_manage<0) {
            if (CTX()->is_in_cli()) {
                $user_code = load_model('sys/UserTaskModel')->get_user_code();
                $sql_user = "select user_id,is_manage from sys_user where user_code=:user_code";
                $sql_values = array(':user_code' => $user_code);
                $user_row = $this->db->get_row($sql_user, $sql_values);
                $this->user_id = $user_row['user_id'];
                 $this->is_manage = 0;
                $sql_role = "select r.role_code from  sys_role r
                    INNER JOIN sys_user_role u ON r.role_id=u.role_id
                    where r.role_code='manage' AND u.user_id=:user_id ";
                $sql_values2 = array(':user_id' => $this->user_id);
                $role_row = $this->db->get_row($sql_role, $sql_values2);
                if(!empty($role_row)){
                     $this->is_manage = 1;
                }

            } else {
                $this->is_manage = CTX()->get_session('is_manage');
            }
        }
    }
	/**
	 *
	 * 方法名                               api_goods_brand_update
	 *
	 * 功能描述                           更新品牌数据
	 *
	 * @author      BaiSon PHP R&D
	 * @date        2015-06-15
	 * @param       array $param
	 *              array(
	 *                  必选: 'brand_code', 'brand_name',
	 *                  可选: 'remark'
	 *                 )
	 * @return      json [string status, obj data, string message]
	 *              {"status":"1","message":"保存成功"," data":"10146"}
	 */
	public function api_goods_brand_update($param) {
	    //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
	    $key_required = array(
	            's' => array('brand_code', 'brand_name')
	    );
	    //可选字段
	    $key_option = array(
	            's' => array('remark')
	    );
	    $arr_required = array();
	    //验证必选字段是否为空并提取必选字段数据
	    $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);

	    //必填项检测通过
	    if (TRUE == $ret_required['status']) {
	        $arr_option = array();
	        //提取可选字段中已赋值数据
	        $ret_option = valid_assign_array($param, $key_option, $arr_option);

	        //合并数据
	        $arr_deal = array_merge($arr_required, $arr_option);

	        //清空无用数据
	        unset($arr_required);
	        unset($arr_option);

	        //检测是否已经存在spec1
	        $ret = $this->is_exists($arr_deal['brand_code']);
	        if (1 == $ret['status']) {
	            unset($arr_deal['brand_code']);
	            //更新数据
	            $ret = $this->update($arr_deal, $ret['data']['brand_id']);
	        } else {
	            //插入数据
	            $ret = $this->insert($arr_deal);
	        }
	        return $ret;
	    } else {
	        return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
	    }
	}

}


