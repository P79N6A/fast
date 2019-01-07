<?php
/**
 * 条码生成方案相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('base');

class BarcodeGenerateSchemeModel extends TbModel {
	function get_table() {
		return 'base_barcode_generate_scheme';
	}

	/*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {
		
		$sql_join = "";
		$sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
		$select = 'rl.*';
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		foreach ($ret_data['data'] as $k => $row) {
			if($row['order_label_img'] == '1'){
				$ret_data['data'][$k]['img'] = "<img src='assets/images/ok.png'/>";
			}else{
				$ret_data['data'][$k]['img'] = "<img src='assets/images/no.gif'/>";
			}
			
		}
		return $this->format_ret($ret_status, $ret_data);
	}
	
	function get_by_id($id) {
		
		return  $this->get_row(array('scheme_id'=>$id));
	}
	
	/*
	 * 添加新纪录
	 */
	function insert($data) {
		$status = $this->valid($data);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		
		$ret = $this->is_exists($data['name'], 'name');
		if (!empty($ret['data'])) return $this->format_ret(SCHEME_ERROR_UNIQUE_NAME);
		
		return parent::insert($data);
	}
	
	/*
	 * 删除记录
	 * */
	function delete($scheme_id) {
		
		$ret = parent::delete(array('scheme_id'=>$scheme_id));
		return $ret;
	}
	
	/*
	 * 修改纪录
	 */
	function update($data, $scheme_id) {
		$status = $this->valid($data, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		$ret = $this->get_row(array('scheme_id'=>$scheme_id));
		
		if($data['name'] != $ret['data']['name']){
			$ret = $this->is_exists($data['name'], 'name');
			if (!empty($ret['data'])) return $this->format_ret(SCHEME_ERROR_UNIQUE_NAME);
		}
		$ret = parent::update($data, array('scheme_id'=>$scheme_id));
		return $ret;
	}

	/*
	 * 服务器端验证
	 */
	private function valid($data, $is_edit = false) {
		
		if (!isset($data['name']) || !valid_input($data['name'], 'required')) return SCHEME_ERROR_NAME;

		return 1;
	}
	
	private function is_exists($value, $field_name='name') {
		$ret = parent::get_row(array($field_name=>$value));

		return $ret;
	}
	
}



