<?php
/**
 * 会员相关业务
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('member');

class MemberModel extends TbModel {
	function get_table() {
		return 'base_member';
	}

	/*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {
		//print_r($filter);
		$sql_join = "";
		
		$sql_main = "FROM {$this->table} r1 LEFT JOIN base_member_consignee r2 ON r1.member_id = r2.member_id $sql_join WHERE 1";
		//会员名称
		if (isset($filter['user_name']) && $filter['user_name'] != '') {
			$sql_main .= " AND r1.user_name LIKE :user_name ";
			$sql_values[':user_name'] = $filter['user_name'].'%';
		}
		//是否黑名单
		if(isset($filter['is_active']) && $filter['is_active'] != ''){
			$sql_main .= " AND r1.is_active = :is_active ";
			$sql_values[':is_active'] = $filter['is_active'];
		}
		//手机号
		if(isset($filter['tel']) && $filter['tel'] != ''){
			$sql_main .= " AND r1.tel LIKE :tel ";
			$sql_values[':tel'] = $filter['tel'].'%';
		}
		//以下对应收货地址表条件
		//收货人
		if(isset($filter['consignee']) && $filter['consignee'] != ''){
			$sql_main .= " AND r2.consignee LIKE :consignee ";
			$sql_values[':consignee'] = $filter['consignee'].'%';
		}
		$select = 'r1.*,r2.consignee_id,r2.consignee,r2.address,r2.province,r2.city,r2.district';
		//echo $select.' '.$sql_main;
		//$data =  $this->get_page_from_sql($filter, $sql_main, $select);
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		
		return $this->format_ret($ret_status, $ret_data);
	}
	
	function get_by_id($store_id) {
		
		return  $this->get_row(array('store_id'=>$store_id));
	}
	//取销售渠道数据
	function get_sale_channel(){
		$sql = "select sale_channel_id,sale_channel_name FROM base_sale_channel";
		$rs = $this->db->get_all($sql);
		return $rs;
	}
	
	/*
	 * 添加新纪录
	 */
	function insert($store) {
		$status = $this->valid($store);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		
		$ret = $this->is_exists($store['store_code']);
		if ($ret['status'] > 0 && !empty($ret['data'])) return $this->format_ret(STORE_ERROR_UNIQUE_CODE);

		$ret = $this->is_exists($user['store_name'], 'store_name');
		if ($ret['status'] > 0 && !empty($ret['data'])) return $this->format_ret(STORE_ERROR_UNIQUE_NAME);
		
		
		return parent::insert($store);
	}
	//检测是否存在
	private function is_exists($value, $field_name='store_code') {
		$ret = parent::get_row(array($field_name=>$value));
	
		return $ret;
	}
	
	
	/*
	 * 删除记录
	 * */
	function delete($store_id) {
		$ret = parent::delete(array('store_id'=>$store_id));
		return $ret;
	}
	
	/*
	 * 修改纪录
	 */
	function update($store, $store_id) {
		$status = $this->valid($store, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		$ret = $this->get_row(array('store_id'=>$store_id));
		if($store['store_name'] != $ret['data']['store_name']){
			$ret = $this->is_exists($store['store_name'], 'store_name');
			if ($ret['status'] > 0 && !empty($ret['data'])) return $this->format_ret(STORE_ERROR_UNIQUE_NAME);
		}
		$ret = parent::update($store, array('store_id'=>$store_id));
		return $ret;
	}

	

	/*
	 * 服务器端验证
	 */
	private function valid($data, $is_edit = false) {
		if (!$is_edit && (!isset($data['store_code']) || !valid_input($data['store_code'], 'required'))) return STORE_ERROR_CODE;
		if (!isset($data['store_name']) || !valid_input($data['store_name'], 'required')) return STORE_ERROR_NAME;

		return 1;
	}
	
	
	
}


