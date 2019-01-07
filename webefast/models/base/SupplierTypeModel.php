<?php
/**
 * 供应商类型相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('base');

class SupplierTypeModel extends TbModel {
	function get_table() {
		return 'base_supplier_type';
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
			$sql_main .= " AND (rl.supplier_type_code LIKE :code_name or rl.supplier_type_name LIKE :code_name)";
			$sql_values[':code_name'] = $filter['code_name'].'%';
		}
		$select = 'rl.*';
		
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		
		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);
	}

	/**
	 * @param $id
	 * @return array
	 */
	function get_by_id($id) {
		
		return  $this->get_row(array('supplier_type_id'=>$id));
	}

	/**
	 * @param $code
	 * @return array
	 */
	function get_by_code($code) {
		return $this->get_row(array('spec1_code'=>$code));
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
		
		$ret = $this->is_exists($spec1['supplier_type_code']);
		
		if (!empty($ret['data'])) {
			return $this->format_ret(SUPPLIER_TYPE_ERROR_UNIQUE_CODE);
		}
        /*
		$ret = $this->is_exists($spec1['spec1_name'], 'spec1_name');
		if (!empty($ret['data'])) return $this->format_ret(SPEC1_ERROR_UNIQUE_NAME);
		*/
		return parent::insert($spec1);
	}
	
	/*
	 * 删除记录
	 * */
	function delete($spec1_id) {
		//$used = $this->is_used_by_id($spec1_id);
		//if($used){
			//return $this->format_ret(-1,array(),'已经在业务系统中使用，不能删除！');
		//}
		$ret = parent::delete(array('supplier_type_id'=>$spec1_id));
		return $ret;
	}
	//规格1
	function get_spec1(){
		$sql = "select spec1_id,spec1_code,spec1_name FROM {$this->table} ";
		$rs = $this->db->get_all($sql);
		return $rs;
	}
	/*
	 * 修改纪录
	 */
	function update($spec1, $spec1_id) {
		$status = $this->valid($spec1, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		
		
		$ret = parent::update($spec1, array('supplier_type_id'=>$spec1_id));
		return $ret;
	}

	

	/*
	 * 服务器端验证
	 */
	private function valid($data, $is_edit = false) {
		if (!$is_edit && (!isset($data['supplier_type_code']) || !valid_input($data['supplier_type_code'], 'required'))) return SUPPLIER_TYPE_ERROR_CODE;
		if (!isset($data['supplier_type_name']) || !valid_input($data['supplier_type_name'], 'required')) return SUPPLIER_TYPE_ERROR_NAME;

		return 1;
	}
	
	function is_exists($value, $field_name='supplier_type_code') {
		$ret = parent::get_row(array($field_name=>$value));

		return $ret;
	}
	
	/**
	 * 根据id判断在业务系统是否使用
	 * @param int $id
	 * @return boolean 已使用返回true, 未使用返回false
	 */
	public function is_used_by_id($id) {
		$result = $this->get_value("select spec1_code from {$this->table} where spec1_id=:id", array(':id' => $id));
		$code = $result['data'];
		$num = $this->get_num('select * from goods_spec1 where spec1_code=:code', array(':code' => $code));
		if(isset($num['data'])&&$num['data']>0){
			//已经在业务系统使用
			return true;
		}else{
			//尚未在业务系统使用
			return false;
		}
	}
}



