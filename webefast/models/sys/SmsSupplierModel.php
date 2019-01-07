<?php
/**
 * 短信供应商 业务
 *
 * @author huanghy
 */
require_model('tb/TbModel');
require_lang('sys');

class SmsSupplierModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent::__construct($table);
    }

    function get_table() {
        return 'sys_sms_supplier';
    }

    function get_data_list($fld = 'supplier_id,supplier_name') {
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
        if (isset($filter['supplier_code']) && $filter['supplier_code'] != '') {
            $sql_main .= " AND supplier_code = :supplier_code";
            $sql_values[':supplier_code'] = $filter['supplier_code'];
        }

        if (isset($filter['supplier_name']) && $filter['supplier_name'] != '') {
            $sql_main .= " AND supplier_name LIKE :supplier_name";
            $sql_values[':supplier_name'] = $filter['supplier_name'] . '%';
        }

        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $arr = $this->get_row(array('supplier_id' => $id));
        return $arr;
    }

    /**
     * 添加新纪录
     */
    function insert($supplier) {
        $status = $this->valid($supplier);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($supplier['supplier_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) {
            return $this->format_ret('sms_supplier_error_unique_code');
        }

        $ret = $this->is_exists($supplier['supplier_name'], 'supplier_name');
        if ($ret['status'] > 0 && !empty($ret['data'])) {
            return $this->format_ret('sms_supplier_error_unique_name');
        }

        return parent::insert($supplier);
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('is_active' => $active), array('supplier_id' => $id));
        return $ret;
    }

    /**
     * 修改纪录
     */
    function update($supplier, $id) {
        $status = $this->valid($supplier, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('supplier_id' => $id));
        if ($supplier['supplier_name'] != $ret['data']['supplier_name']) {
            $ret = $this->is_exists($supplier['supplier_name'], 'supplier_name');
            if ($ret['status'] > 0 && !empty($ret['data'])) {
                return $this->format_ret('sms_supplier_error_unique_name');
            }
        }

        $ret = parent::update($supplier, array('supplier_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'supplier_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    function supplier_sel() {
        $sql = "select supplier_id,supplier_name from supplier where is_active = 1";
        $db_arr = $this->db->getAll($sql);
        $arr = load_model('tb/GetSqlRelationData')->arr_tran_keys($db_arr, $fld);
    }

    /**
     * 删除纪录
     */
    function delete($id) {
        $ret = parent::delete(array('supplier_id' => $id));
        return $ret;
    }
}
