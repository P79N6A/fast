<?php
/**

 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('base');

class RecordTypeModel extends TbModel {
        public $record_type_status = array(
            0=>'采购进货',
            1=>'采购退货',
            2=>'批发发货',
            3=>'批发退货', 
            8=>'库存调整',
        );
	function get_table() {
		return 'base_record_type';
	}

	/*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {
		$sql_join = "";
                $sql_values = array();
                if (isset($filter['record_type_property']) && $filter['record_type_property'] != '') {
			$sql_type = " AND rl.record_type_property = :record_type_property ";
			$sql_values[':record_type_property'] = $filter['record_type_property'];
                }else{
                        $sql_type = " and (record_type_property=1 or record_type_property=0 or record_type_property=2 or record_type_property=3 or record_type_property=8) ";
                }
		$sql_main = "FROM {$this->table} rl $sql_join WHERE 1 ".$sql_type;		
		//仓库名称或代码
                if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
                    $filter[$filter['keyword_type']] = trim($filter['keyword']);
                }
		if (isset($filter['record_type_name']) && $filter['record_type_name'] != '') {
			$sql_main .= " AND rl.record_type_name LIKE :record_type_name ";
			$sql_values[':record_type_name'] = '%'.$filter['record_type_name'].'%';
		}
		if (isset($filter['record_type_code']) && $filter['record_type_code'] != '') {
			$sql_main .= " AND rl.record_type_code LIKE :record_type_code ";
			$sql_values[':record_type_code'] = '%'.$filter['record_type_code'].'%';
		}
                if (isset($filter['remark']) && $filter['remark'] != '') {
			$sql_main .= " AND rl.remark LIKE :remark ";
			$sql_values[':remark'] = '%'.$filter['remark'].'%';
		}
		
		$select = 'rl.*';
		
		//$data =  $this->get_page_from_sql($filter, $sql_main, $select);
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
                foreach($data['data'] as &$val){
                    $val['record_type_property_name'] = $this->record_type_status[$val['record_type_property']];
                }
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		
		return $this->format_ret($ret_status, $ret_data);
		
	}

	/**
	 * @param $id
	 * @return array
	 */
	function get_by_id($id) {
		
		return  $this->get_row(array('record_type_id'=>$id));
	}

	/**
	 * @param $code
	 * @return array
	 */
	function get_by_code($code) {
		return $this->get_row(array('record_type_code'=>$code));
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
		
		$ret = $this->is_exists($spec1['record_type_code'],'record_type_code');
		
		if (!empty($ret['data'])) {
			return $this->format_ret(-1,'','RECORD_TYPE_ERROR_UNIQUE_CODE');
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
		$ret = parent::delete(array('record_type_id'=>$spec1_id));
		return $ret;
	}
	
	/*
	 * 修改纪录
	 */
	function update($spec1, $spec1_id) {
		$status = $this->valid($spec1, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		
		
		$ret = parent::update($spec1, array('record_type_id'=>$spec1_id));
		return $ret;
	}

	

	/*
	 * 服务器端验证
	 */
	private function valid($data, $is_edit = false) {
		if (!$is_edit && (!isset($data['record_type_code']) || !valid_input($data['record_type_code'], 'required'))) return SUPPLIER_ERROR_CODE;
		if (!isset($data['record_type_name']) || !valid_input($data['record_type_name'], 'required')) return SUPPLIER_ERROR_NAME;

		return 1;
	}
	
	function is_exists($value1, $field_name1='record_type_code') {
		$ret = parent::get_row(array($field_name1=>$value1));

		return $ret;
	}
	
	
}




