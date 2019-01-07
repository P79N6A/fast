<?php
/**
* 订单挂起原因相关业务
*
* @author huanghy
*/
require_model('tb/TbModel');
require_lang('sys');

class OrderPauseReasonModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this -> get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'base_order_pause_reason';
    }

    /**
    * 根据条件查询数据
    */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
        if (isset($filter['pause_reason_code']) && $filter['pause_reason_code'] != '') {
            $sql_main .= " AND pause_reason_code = :pause_reason_code";
            $sql_values[':pause_reason_code'] = $filter['pause_reason_code'];
        }

        if (isset($filter['pause_reason_name']) && $filter['pause_reason_name'] != '') {
            $sql_main .= " AND pause_reason_name LIKE :pause_reason_name";
            $sql_values[':pause_reason_name'] = $filter['pause_reason_name'] . '%';
        }

        $select = '*';

        $data = $this -> get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this -> format_ret($ret_status, $ret_data);
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this -> format_ret('error_params');
        }
        $ret = parent :: update(array('is_active' => $active), array('pause_reason_id' => $id));
        return $ret;
    }

    /**
    * 添加新纪录
    */
    function insert($order_pause) {
        $status = $this -> valid($order_pause);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> is_exists($order_pause['pause_reason_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(ORDERPAUSEREASON_ERROR_UNIQUE_CODE);

        $ret = $this -> is_exists($order_pause['pause_reason_name'], 'pause_reason_name');
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(ORDERPAUSEREASON_ERROR_UNIQUE_NAME);

        return parent :: insert($order_pause);
    }

    /**
    * 修改纪录
    */
    function update($order_pause, $id) {
        $status = $this -> valid($order_pause, true);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> get_row(array('pause_reason_id' => $id));
        if ($order_pause['pause_reason_name'] != $ret['data']['pause_reason_name']) {
            $ret = $this -> is_exists($order_pause['pause_reason_name'], 'pause_reason_name');
            if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(ORDERPAUSEREASON_ERROR_UNIQUE_NAME);
        }
        $ret = parent :: update($order_pause, array('pause_reason_id' => $id));
        return $ret;
    }

    /**
    * 删除纪录
    */
    function delete($id) {
        $ret = parent :: delete(array('pause_reason_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'pause_reason_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }
}
