<?php

/**
 * 支付方式相关业务
 *
 * @author huanghy
 */
require_model('tb/TbModel');
require_lang('sys');

class PaymentModel extends TbModel {

    /**
      static public $payment_type = array(
      1=>'担保交易',
      2=>'货到付款',
      3=>'款到发货',
      );
     * 
     */
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'base_pay_type';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
        if (isset($filter['pay_type_code']) && $filter['pay_type_code'] != '') {
            $sql_main .= " AND pay_type_code LIKE :pay_type_code";
            $sql_values[':pay_type_code'] = $filter['pay_type_code'] . '%';
        }

        if (isset($filter['pay_type_name']) && $filter['pay_type_name'] != '') {
            $sql_main .= " AND pay_type_name LIKE :pay_type_name";
            $sql_values[':pay_type_name'] = $filter['pay_type_name'] . '%';
        }

        $select = "pay_type_id,pay_type_code,pay_type_name,remark,is_fetch,relation_code,status,trim(LEADING ',' from concat_ws(',',if(is_vouch,'担保交易',''),if(is_cod,'货到付款','款到发货'))) as pay_type";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }
    //新增记录
    function insert($request){
        $pay_type_code = $request['pay_type_code'];
        $pay_type_name = $request['pay_type_name'];
        $pay_type = $request['pay_type'];
        $remark = $request['remark'];
        switch ($pay_type){
            case 'cod':
                $is_cod = 1;
                $is_vouch = 0;
            case 'secured':
                $is_cod = 0;
                $is_vouch = 1;
            default:
                $is_cod = 0;
                $is_vouch = 0;
        }
        $array = array('pay_type_code' => $pay_type_code, 'pay_type_name' => $pay_type_name, 'is_vouch' => $is_vouch, 'is_cod' => $is_cod, 'remark' => $remark);     
        $ret = parent::insert($array);
        return $ret;
    }
    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent :: update(array('is_active' => $active), array('pay_type_id' => $id));
        return $ret;
    }

    /**
     * 修改纪录
     */
    function update($Payment, $id) {
        $status = $this->valid($Payment, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
//        $ret = $this->get_row(array('pay_type_id' => $id));
//        if (isset($Payment['pay_type_name']) && $Payment['pay_type_name'] != $ret['data']['pay_type_name']) {
//            $ret = $this->is_exists($Payment['pay_type_name'], 'pay_type_name');
//            if ($ret['status'] > 0 && !empty($ret['data']))
//                return $this->format_ret('SHOP_ERROR_UNIQUE_NAME');
//        }
        $ret = parent :: update($Payment, array('pay_type_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'pay_type_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 服务器端验证
     */
    private function valid($data, $is_edit = false) {
        return 1;
    }

    public function get_pay_type(){
        $pay_type_info = $this->db->get_all("select pay_type_code,pay_type_name from {$this->table}");
        return $pay_type_info;
    }
    
}
