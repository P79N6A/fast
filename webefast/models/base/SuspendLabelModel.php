<?php
/**
 * 订单标签相关业务
 *
 * @author huanghy
 */
require_model('tb/TbModel');
require_lang('sys');

class SuspendLabelModel extends TbModel {
	public function __construct($table = '', $db = '') {
		$table = $this->get_table();
		parent::__construct($table);
	}

	function get_table() {
		return 'base_suspend_label';
	}

	/**
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {
		$sql_values = array();
		$sql_main = "FROM {$this->table}  WHERE 1 AND suspend_label_code<>'wait_check_refund' ";
		if (isset($filter['suspend_label_code']) && $filter['suspend_label_code'] != '') {
			$sql_main .= " AND suspend_label_code = :suspend_label_code";
			$sql_values[':suspend_label_code'] = $filter['suspend_label_code'];
		}

		if (isset($filter['suspend_label_name']) && $filter['suspend_label_name'] != '') {
			$sql_main .= " AND suspend_label_name LIKE :suspend_label_name";
			$sql_values[':suspend_label_name'] = $filter['suspend_label_name'] . '%';
		}

		$select = '*';

		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		foreach ($ret_data['data'] as $key => $row) {
			if($row['is_sys'] == 1)
	    	$ret_data['data'][$key]['is_sys_html'] = "<img src='assets/images/ok.png'/>";
	    	else 
	    	$ret_data['data'][$key]['is_sys_html'] = "";
	    	if($row['cancel_suspend_time'] == "0000-00-00 00:00:00" )
	    	$ret_data['data'][$key]['cancel_suspend_time'] = '';
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

        $ret = $this -> is_exists($order_label['suspend_label_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(SUSPENDLABEL_ERROR_UNIQUE_CODE);

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

		$ret = $this->get_row(array('suspend_label_id' => $id));

		$ret = parent::update($order_label, array('suspend_label_id' => $id));
		return $ret;
	}
	
	/**
	 * 根据 id 查询数据
	 */
	function get_by_id($id) {
		$ret = parent::get_row(array('suspend_label_id' => $id));
    	if($ret['data']['cancel_suspend_time'] == "0000-00-00 00:00:00" )
    	$ret['data']['cancel_suspend_time'] = '';
		return $ret;
	}
	
    /**
    * 删除纪录
    */
    function delete($id) {
        $ret = parent :: delete(array('suspend_label_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'suspend_label_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }

}