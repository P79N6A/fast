<?php
/**
* 仓库库位档案 相关业务
*
* @author huanghy
*/
require_model('tb/TbModel');
require_lang('sys');

class StoreSeatModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this -> get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'base_store_seat';
    }

    /**
    * 根据条件查询数据
    */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
        if (isset($filter['store_seat_code']) && $filter['store_seat_code'] != '') {
            $sql_main .= " AND store_seat_code=:store_seat_code";
            $sql_values[':store_seat_code'] = $filter['store_seat_code'];
        }

        if (isset($filter['store_seat_name']) && $filter['store_seat_name'] != '') {
            $sql_main .= " AND store_seat_name LIKE :store_seat_name";
            $sql_values[':store_seat_name'] = $filter['store_seat_name'] . '%';
        }

        $select = '*';

        $data = $this -> get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this -> format_ret($ret_status, $ret_data);
    }

    /**
    * 添加新纪录
    */
    function insert($store_seat) {
        $status = $this -> valid($store_seat);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> is_exists($store_seat['store_seat_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(STORESEAT_ERROR_UNIQUE_CODE);

        $ret = $this -> is_exists($store_seat['store_seat_name'], 'store_seat_name');
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(STORESEAT_ERROR_UNIQUE_NAME);

        return parent :: insert($store_seat);
    }

    /**
    * 修改纪录
    */
    function update($store_seat, $id) {
        $status = $this -> valid($store_seat, true);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> get_row(array('store_seat_id' => $id));
        if ($store_seat['store_seat_name'] != $ret['data']['store_seat_name']) {
            $ret = $this -> is_exists($store_seat['store_seat_name'], 'store_seat_name');
            if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(STORESEAT_ERROR_UNIQUE_NAME);
        }
        $ret = parent :: update($store_seat, array('store_seat_id' => $id));
        return $ret;
    }

    /**
    * 删除纪录
    */
    function delete($id) {
        $ret = parent :: delete(array('store_seat_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'store_seat_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }
}
