<?php
/**
 * 会员收货地址信息相关业务
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('member');

class ConsigneeModel extends TbModel {
	function get_table() {
		return 'base_member_consignee';
	}

	
	function get_by_id($member_id) {
		
		return  $this->get_row(array('member_id'=>$member_id));
	}
	//取收货地址数据
	function get_all_consignee($member_id){
		
		$sql = "select * FROM base_member_consignee where member_id = '{$member_id}'";
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



