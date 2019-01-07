<?php

require_model('tb/TbModel');

class PaymentaccountModel extends TbModel {

    function get_table() {
        return 'payment_type';
    }

    function get_type_by_page($filter) {
        $sql_main = "FROM {$this->table} rl WHERE 1";

        $sql_values = array();

        $select = 'rl.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_account_by_page($filter) {
        $sql_main = "FROM payment_account rl WHERE 1";

        $sql_values = array();

        if (isset($filter['account_name']) && $filter['account_name'] != '') {
            $sql_main .= ' AND account_name LIKE :account_name ';
            $sql_values['account_name'] = "%{$filter['account_name']}%";
        }
        $select = 'rl.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function add($request) {
        $code = "BC" . date("Y") . date('m') . date('d') . date('i') . date('s');
        $ret = parent::insert_exp('payment_account', array('account_code' => $code, 'account_name' => $request['account_name'], 'account_bank' => $request['account_bank'], 'bank_code' => $request['bank_code']));
        return $ret;
    }

    function delete($id) {
        $sql = "select count(1) from payment_account where id=:id";
        $row = $this->db->get_row($sql, array(':id' => $id));
        if (empty($row)) {
            return $this->format_ret('-1', '', '该账户不存在');
        }
        $sql_del = "delete from payment_account where id=:id";
        $this->db->query($sql_del, array(':id' => $id));
        return $this->format_ret('1', '', '删除成功');
    }

    function get_by_account() {
        $sql = "SELECT account_code,account_name FROM payment_account";
        $ret = $this->db->get_all($sql);
        $data = array_merge(array(array('', '请选择')), $ret);
        return $data;
    }
    
    function is_exists($filter, $where = 'id', $select = '*') {
        $sql = "SELECT {$select} FROM payment_account WHERE {$where} = :{$where}";
        $ret = $this->db->get_row($sql, array(':' . $where => $filter));
        return $ret;
    } 
    
    function update($filter) {
        $params = array(
            'account_name' => $filter['account_name'],
            'account_bank' => $filter['account_bank'],
            'bank_code' => $filter['bank_code']
        );
        $ret = $this->update_exp('payment_account', $params, array('id' => $filter['id']));
        return $ret;
    }

}
?>

