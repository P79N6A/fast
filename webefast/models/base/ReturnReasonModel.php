<?php

/**
 * 退货原因相关业务
 *
 * @author huanghy
 */
require_model('tb/TbModel');
require_lang('sys');

class ReturnReasonModel extends TbModel {

    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'base_return_reason';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table}  WHERE 1 ";
        if (isset($filter['return_reason_code']) && $filter['return_reason_code'] != '') {
            $sql_main .= " AND return_reason_code = :return_reason_code";
            $sql_values[':return_reason_code'] = $filter['return_reason_code'];
        }

        if (isset($filter['return_reason_name']) && $filter['return_reason_name'] != '') {
            $sql_main .= " AND return_reason_name LIKE :return_reason_name";
            $sql_values[':return_reason_name'] = $filter['return_reason_name'] . '%';
        }

        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        $return_reason_type_map = array('1' => '销售', '2' => '采购', '3' => '批发');
        foreach ($ret_data['data'] as $k => $row) {
            $ret_data['data'][$k]['return_reason_type_txt'] = $return_reason_type_map[$row['return_reason_type']];
        }
        return $this->format_ret($ret_status, $ret_data);
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent :: update(array('is_active' => $active), array('return_reason_id' => $id));
        return $ret;
    }

    /**
     * 添加新纪录
     */
    function insert($return_reason) {
        $status = $this->valid($return_reason);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($return_reason['return_reason_code']);
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(RETURNREASON_ERROR_UNIQUE_CODE);

        $ret = $this->is_exists($return_reason['return_reason_name'], 'return_reason_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(RETURNREASON_ERROR_UNIQUE_NAME);

        return parent :: insert($return_reason);
    }

    /**
     * 修改纪录
     */
    function update($return_reason, $id) {
        $status = $this->valid($return_reason, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('return_reason_id' => $id));
        if ($return_reason['return_reason_name'] != $ret['data']['return_reason_name']) {
            $ret = $this->is_exists($return_reason['return_reason_name'], 'return_reason_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret(RETURNREASON_ERROR_UNIQUE_NAME);
        }
        $ret = parent :: update($return_reason, array('return_reason_id' => $id));
        return $ret;
    }

    /**
     * 删除纪录
     */
    function delete($id) {
        $ret = parent :: delete(array('return_reason_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'return_reason_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }

    function get_by_code($return_reason_code) {
        $sql = "select * from $this->table where return_reason_code=:return_reason_code";
        $reason_row = $this->db->get_row($sql, array(":return_reason_code" => $return_reason_code));
        return $reason_row;
    }

    /**
     * 生成退货原因编码
     * @return string 退货原因编码
     */
    function create_return_reason_code() {
        $sql = "SELECT return_reason_id FROM {$this->table} ORDER BY return_reason_id DESC";
        $id = $this->db->get_value($sql);
        if (!empty($id)) {
            $djh = intval($id) + 1;
        } else {
            $djh = 1;
        }

        require_lib('comm_util', true);
        $new_code = "THYY" . add_zero($djh);
        $sql = "SELECT return_reason_id FROM {$this->table} WHERE return_reason_code=:return_reason_code ";
        $sql_value = array(':return_reason_code' => $new_code);
        $id = $this->db->get_value($sql, $sql_value);
        if (empty($id) || $id === false) {
            return $new_code;
        } else {
            return $this->create_return_reason_code();
        }
    }

    /**
     * 获取退货原因编码
     * @param string $return_reason_name 退货原因
     * @return array 退货原因编码
     */
    function get_return_reason_code($return_reason_name) {
        $sql = "SELECT return_reason_code,return_reason_name FROM {$this->table} WHERE return_reason_name LIKE :return_reason_name";
        $data = $this->db->get_row($sql, array(':return_reason_name' => $return_reason_name));
        if (!empty($data)) {
            return $this->format_ret(1, $data['return_reason_code']);
        }

        $return_reason_code = $this->create_return_reason_code();
        $data = array(
            'return_reason_code' => $return_reason_code,
            'return_reason_name' => $return_reason_name,
            'return_reason_type' => 1,
            'is_active' => 1,
            'is_sys' => 0,
            'remark' => '转单生成',
        );
        $ret = $this->insert($data);
        $ret['data'] = $return_reason_code;
        
        return $ret;
    }

}
