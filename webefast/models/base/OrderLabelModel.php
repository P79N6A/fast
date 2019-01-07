<?php
/**
 * 订单标签相关业务
 *
 * @author huanghy
 */
require_model('tb/TbModel');
require_lang('sys');

class OrderLabelModel extends TbModel {
	public function __construct($table = '', $db = '') {
		$table = $this->get_table();
		parent::__construct($table);
	}

	function get_table() {
		return 'base_order_label';
	}

	/**
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {
		$sql_values = array();
		$sql_main = "FROM {$this->table}  WHERE 1";
		if (isset($filter['order_label_code']) && $filter['order_label_code'] != '') {
			$sql_main .= " AND order_label_code = :order_label_code";
			$sql_values[':order_label_code'] = $filter['order_label_code'];
		}

		if (isset($filter['order_label_name']) && $filter['order_label_name'] != '') {
			$sql_main .= " AND order_label_name LIKE :order_label_name";
			$sql_values[':order_label_name'] = $filter['order_label_name'] . '%';
		}

		$select = '*';

		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		foreach ($ret_data['data'] as $k => $row) {
			if($row['is_sys'] == 0){
                            $ret_data['data'][$k]['is_sys_html'] = '自定义';
                        }else{
                            $ret_data['data'][$k]['is_sys_html'] = '系统内置';
                        }

	 
		}
		return $this->format_ret($ret_status, $ret_data);
	}
	
    /**
    * 添加新纪录
    */
    function insert($order_label) {
        $status = $this -> valid($order_label);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> is_exists($order_label['order_label_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret('-1','','ORDERLABEL_ERROR_UNIQUE_CODE');

        return parent :: insert($order_label);
    }
    
	/**
	 * 修改纪录
	 */
	function update($order_label, $id) {
		$status = $this->valid($order_label, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}

		$ret = $this->get_row(array('order_label_id' => $id));

		$ret = parent::update($order_label, array('order_label_id' => $id));
		return $ret;
	}
	
	/**
	 * 根据 id 查询数据
	 */
	function get_by_id($id) {
		$ret = parent::get_row(array('order_label_id' => $id));
		return $ret;
	}
	
    /**
    * 删除纪录
    */
    function delete($id) {
        $ret = parent :: delete(array('order_label_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'order_label_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }
    public function get_select(){
       $ret = $this->get_all(array(),'order_label_code,order_label_name');
       $ret_data = array();
       foreach($ret['data'] as $val){
          $ret_data[] = array($val['order_label_code'],$val['order_label_name']); 
       }
       return $ret_data;
    }


}