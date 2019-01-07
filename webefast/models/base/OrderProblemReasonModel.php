<?php
/**
* 订单问题原因相关业务
*
* @author huanghy
*/
require_model('tb/TbModel');
require_lang('sys');

class OrderProblemReasonModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this -> get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'base_order_problem_reason';
    }

    /**
    * 根据条件查询数据
    */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl WHERE 1";
        if (isset($filter['problem_reason_code']) && $filter['problem_reason_code'] != '') {
            $sql_main .= " AND problem_reason_code=:problem_reason_code";
            $sql_values[':problem_reason_code'] = $filter['problem_reason_code'];
        }

        if (isset($filter['problem_reason_name']) && $filter['problem_reason_name'] != '') {
            $sql_main .= " AND rl.problem_reason_name LIKE :problem_reason_name";
            $sql_values[':problem_reason_name'] = $filter['problem_reason_name'] . '%';
        }

        $select = 'rl.*';
        $data = $this -> get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this -> format_ret($ret_status, $ret_data);
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this -> format_ret('error_params');
        }
        $ret = parent :: update(array('is_active' => $active), array('problem_reason_id' => $id));
        return $ret;
    }

    /**
    * 添加新纪录
    */
    function insert($order_problem) {
        $status = $this -> valid($order_problem);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> is_exists($order_problem['problem_reason_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(ORDERPROBLEMREASON_ERROR_UNIQUE_CODE);

        $ret = $this -> is_exists($order_problem['problem_reason_name'], 'problem_reason_name');
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(ORDERPROBLEMREASON_ERROR_UNIQUE_NAME);

        return parent :: insert($order_problem);
    }

    /**
    * 修改纪录
    */
    function update($order_problem, $id) {
        $status = $this -> valid($order_problem, true);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> get_row(array('problem_reason_id' => $id));
        if ($order_problem['problem_reason_name'] != $ret['data']['problem_reason_name']) {
            $ret = $this -> is_exists($order_problem['problem_reason_name'], 'problem_reason_name');
            if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(ORDERPROBLEMREASON_ERROR_UNIQUE_NAME);
        }
        $ret = parent :: update($order_problem, array('problem_reason_id' => $id));
        return $ret;
    }

    /**
    * 删除纪录
    */
    function delete($id) {
        $ret = parent :: delete(array('problem_reason_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'problem_reason_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }
}
