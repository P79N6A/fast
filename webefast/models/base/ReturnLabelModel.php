<?php
/**
* 退单标签相关业务
*
* @author huanghy
*/
require_model('tb/TbModel');
require_lang('sys');

class ReturnLabelModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this -> get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'base_return_label';
    }

    /**
    * 根据条件查询数据
    */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table}  WHERE 1";
        if (isset($filter['return_label_code']) && $filter['return_label_code'] != '') {
            $sql_main .= " AND return_label_code = :return_label_code";
            $sql_values[':return_label_code'] = $filter['return_label_code'];
        }

        if (isset($filter['return_label_name']) && $filter['return_label_name'] != '') {
            $sql_main .= " AND return_label_name LIKE :return_label_name";
            $sql_values[':return_label_name'] = $filter['return_label_name'] . '%';
        }

        $select = '*';

        $data = $this -> get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach($data['data'] as &$value){
            $value['is_sys_html'] = ($value['is_sys_html'] == 0) ? '否' : '是';
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
//        foreach($ret_data['data'] as $k => $row) {
//            $ret_data['data'][$k]['return_label_img_htm'] = "<img width='20px' height = '20px'   src='assets/img/return_label/{$row['return_label_img']}'/>";
//        }
        return $this -> format_ret($ret_status, $ret_data);
    }

    /**
    * 添加新纪录
    */
    function insert($return_label) {
        $status = $this -> valid($return_label);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> is_exists($return_label['return_label_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(-1,'','RETURNLABEL_ERROR_UNIQUE_CODE');

        return parent :: insert($return_label);
    }
    /**
    * 修改纪录
    */
    function update($return_label, $id) {
        $status = $this -> valid($return_label, true);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> get_row(array('return_label_id' => $id));

        $ret = parent :: update($return_label, array('return_label_id' => $id));
        return $ret;
    }
    
	/**
	 * 根据 id 查询数据
	 */
	function get_by_id($id) {
		$ret = parent::get_row(array('return_label_id' => $id));
		return $ret;
	}
	
    /**
    * 删除纪录
    */
    function delete($id) {
        $ret = parent :: delete(array('return_label_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'return_label_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }
    public function get_select(){
       $ret = $this->get_all(array(),'return_label_code,return_label_name');
       $ret_data = array();
       foreach($ret['data'] as $val){
          $ret_data[] = array($val['return_label_code'],$val['return_label_name']); 
       }
       $ret_data[] = array('none','无标签'); 
       return $ret_data;
    }
}
