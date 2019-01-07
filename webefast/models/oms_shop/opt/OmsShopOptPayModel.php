<?php

require_model('oms_shop/opt/OmsShopOptAbs');

class OmsShopOptPayModel extends OmsShopOptAbs {

    function __construct() {
        parent::__construct('oms_shop_sell_record_pay');
    }

    function check(&$sell_record, &$sell_record_detail) {
        $this->sell_record = &$sell_record;
        $this->sell_record_detail = &$sell_record_detail;

        if ($this->sell_record['cancel_status'] == 1) {
            return $this->format_ret(-1, '', '单据已经作废不能支付');
        }
        if ($this->sell_record['pay_status'] == 2) {
            return $this->format_ret(-1, '', '单据已经支付，不能重复支付');
        }

        return $this->format_ret(1);
    }

    function opt(&$params) {
        $this->params = &$params;
        $ret_check = $this->check_params($this->params);
        if ($ret_check['status'] < 1) {
            return $ret_check;
        }

        $sell_record_pay_data = &$ret_check['data'];
        $sell_record_pay_data['record_code'] = $this->sell_record['record_code'];

        $ret_pay = $this->save_pay_money($sell_record_pay_data);
        if ($ret_pay['status'] < 1) {
            return $ret_pay;
        }
        
        $this->set_opt_log();
        
        $inv_status = 0;
        if ($this->new_sell_record['pay_status'] == 2) {
            $inv_status = ( $this->new_sell_record['send_way'] == 1 ) ? 6 : 1;
        }

        //库存处理
        if ($inv_status > 0) {
            $inv_params = array('inv_status' => $inv_status);
            $ret_inv = $this->opt_other('inv', $inv_params);
            if ($ret_inv['status'] < 1) {
                return $ret_inv;
            }
            $this->set_inv_record_info($ret_inv['data']);
        }

        //更新单据
        $where = " pay_status<>2 ";
        $status = $this->save_record($where);
        if ($status === FALSE) {
            //要事务回滚
            return $this->format_ret(-1, '', "付款保存异常");
        }

        //特殊情况：发货处理
        if ($inv_status == 2) {
            $send_params = array();
            $ret_inv = $this->opt_other('send', $send_params, 1);
        }

        return $this->format_ret(1);
    }

    function set_inv_record_info($inv_data) {
        $this->new_sell_record = array_merge($this->new_sell_record, $inv_data);
    }

    //参数检查
    function check_params(&$params) {
        $sell_record_pay_data = array();
        $msg = '';
        $status = 1;
        if (empty($params['pay_code'])) {
            $msg .= '支付代码不能为空,';
            $status = -1;
        } else {
            $sell_record_pay_data['pay_code'] = $params['pay_code'];
        }
        if (empty($params['pay_money']) || $params['pay_money'] <= 0) {
            $msg .= '支付金额必须大于0,';
            $status = -1;
        } else {
            $sell_record_pay_data['pay_money'] = $params['pay_money'];
        }

        $sell_record_pay_data['pay_account'] = isset($params['pay_account']) ? $params['pay_account'] : '';
        $sell_record_pay_data['pay_serial_no'] = isset($params['pay_serial_no']) ? $params['pay_serial_no'] : '';

        return $this->format_ret($status, $sell_record_pay_data, $msg);
    }

    function get_sell_record_pay_money() {
        $sql = " select sum(pay_money) from oms_shop_sell_record_pay  where record_code=:record_code ";
        $sql_value = array(':record_code' => $this->sell_record['record_code']);
        return $this->db->get_value($sql, $sql_value);
    }

    private function save_pay_money($sell_record_pay_data) {

        $ret_pay_check = $this->insert($sell_record_pay_data);
        if ($ret_pay_check['status'] < 1) {
            //要事务回滚
            return $this->format_ret(-1, '', "支付报错异常");
        }

        $pay_money = $this->get_sell_record_pay_money();
        if ($pay_money > $this->sell_record['payable_amount']) {
            //要事务回滚
            return $this->format_ret(-1, '', "付款金额({$pay_money})大于应付金额({$this->sell_record['payable_amount']})");
        }

        $this->new_sell_record['pay_status'] = ($pay_money < $this->sell_record['payable_amount']) ? 1 : 2;
        $this->new_sell_record['pay_time'] = date('Y-m-d H:i:s');
        $this->new_sell_record['buyer_real_amount'] = $pay_money;
        //默认买家自提
        $this->new_sell_record['send_way'] = isset($this->sell_record['send_way']) ? $this->sell_record['send_way'] : 1;

        return $this->format_ret(1);
    }

    function set_opt_log($dsc = '') {

        $log_data['action_code'] = 'pay';
        $log_data['action_name'] = '付款';
        $log_data['action_desc'] = '付款金额：' . $this->params['pay_money'];

        $this->set_log($log_data);
    }

}
