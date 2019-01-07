<?php

require_model('tb/TbModel');

/**
 * Description of CustomersEncrypModel
 *
 * @author wq
 */
class CustomersSecurityLogModel extends TbModel {

    function __construct() {
        parent::__construct('crm_customer_address_encrypt_log');
    }

    function add_log($data) {
        $data['action_time'] = time();
        $data['user_code'] = CTX()->get_session('user_code');
        return $this->insert($data);
    }

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        $sql_values = array();
        if (isset($filter['customer_code'])) {
   
               $sql_main.=" AND customer_code =:customer_code";
                $sql_values['customer_code'] = $filter['customer_code']; 
    
      
        }
        if (isset($filter['customer_address_id'])) {
            $customer_address_id_arr = $this->get_customer_address_id($filter['customer_code']);
            if (!empty($customer_address_id_arr)) {
                $sql_main.=" AND customer_address_id = :customer_address_id ";
                $sql_values['customer_address_id'] = $filter['customer_address_id'];
            }
        }
        $select = ' * ';
        $sql_main .= " order by id desc";
        //echo $sql_main;
        $record_type_arr = array(
            'sell_record' => '订单',
            'sell_return' => '退单',
        );
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            $val['record_type'] = $record_type_arr[$val['record_type']];
            $val['action_time'] = date('Y-m-d H:i:s', $val['action_time']);
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_customer_address_id($customer_code) {
        $sql = "select customer_address_id from crm_customer_address where customer_code=:customer_code";
        $data = $this->db->get_all($sql, array(':customer_code' => $customer_code));
        $customer_address_id_arr = array();
        foreach ($data as $val) {
            $customer_address_id_arr[] = $val['customer_address_id'];
        }
        return $customer_address_id_arr;
    }

}
