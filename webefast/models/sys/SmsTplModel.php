<?php
/**
* 短信模板 相关业务
*
* @author dfr
*/
require_model('tb/TbModel');
require_lang('sys');

class SmsTplModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'sys_sms_tpl';
    }

    function get_data_list($fld = 'id,tpl_name') {
        $sql = "select $fld from {$this->table} where is_active = 1";
        $arr = $this->db->get_all($sql);
        return $arr;
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
        if (isset($filter['tpl_type']) && $filter['tpl_type'] != '') {
            $sql_main .= " AND tpl_type = :tpl_type";
            $sql_values[':tpl_type'] = $filter['tpl_type'];
        }

        if (isset($filter['tpl_name']) && $filter['tpl_name'] != '') {
            $sql_main .= " AND tpl_name LIKE :tpl_name";
            $sql_values[':tpl_name'] = $filter['tpl_name'] . '%';
        }

        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $arr = $this->get_row(array('id' => $id));
        return $arr;
    }
     /**
     * 修改纪录
     */
    function update($supplier, $id) {
        $status = $this->valid($supplier, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('id' => $id));
        if ($supplier['tpl_name'] != $ret['data']['tpl_name']) {
            $ret = $this->is_exists($supplier['tpl_name'], 'tpl_name');
            if ($ret['status'] > 0 && !empty($ret['data'])) {
                return $this->format_ret('sms_tpl_error_unique_name');
            }
        }

        $ret = parent::update($supplier, array('id' => $id));
        return $ret;
    }

   private function is_exists($value, $field_name = 'tpl_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    
    
 	/**
     * 添加新纪录
     */
    function insert($supplier) {
        $status = $this->valid($supplier);
        if ($status < 1) {
            return $this->format_ret($status);
        }

//        $ret = $this->is_exists($supplier['supplier_code']);
//        if ($ret['status'] > 0 && !empty($ret['data'])) {
//            return $this->format_ret('sms_supplier_error_unique_code');
//        }

        $ret = $this->is_exists($supplier['tpl_name'], 'tpl_name');
        if ($ret['status'] > 0 && !empty($ret['data'])) {
            return $this->format_ret('sms_tpl_error_unique_name');
        }

        return parent::insert($supplier);
    }


    /**
    * 删除记录
    */
    function delete($id) {
        $ret = parent :: delete(array('id' => $id));
        return $ret;
    }
    
    
    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('is_active' => $active), array('id' => $id));
        return $ret;
    }


}
    