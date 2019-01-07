<?php

require_model('oms_shop/opt/OmsShopOptAbs');

class OmsShopOptCancelPayModel extends OmsShopOptAbs {

    function __construct() {
        parent::__construct('oms_shop_sell_record_pay');
    }

    function check(&$sell_record, &$sell_record_detail) {
        $this->sell_record = &$sell_record;
        $this->sell_record_detail = &$sell_record_detail;


        if ($this->sell_record['send_status'] == 1) {
            return $this->format_ret(-1, '', '单据已经发货不能取消付款');
        }
        if ($this->sell_record['cancel_status'] == 1) {
            return $this->format_ret(-1, '', '单据已经作废不能取消付款');
        }
        if ($this->sell_record['pay_status'] == 0) {
            return $this->format_ret(-1, '', '单据未付款不需要取消付款');
        }

        return $this->format_ret(1);
    }

    function opt(&$params) {
        $this->params = &$params;
        $ret_check = $this->check_params($this->params);
        if ($ret_check['status'] < 1) {
            return $ret_check;
        }


        $ret_pay = $this->del_pay_money();
        if ($ret_pay['status'] < 1) {
            return $ret_pay;
        }

        $this->set_opt_log($this->sell_record['buyer_real_amount']);

        if ($this->sell_record['pay_status'] != 1) {
            //库存取消锁定
            $inv_params = array('inv_status' => 0);
            $ret_inv = $this->opt_other('inv', $inv_params);
            if ($ret_inv['status'] < 1) {
                return $ret_inv;
            }
            $this->set_inv_record_info($ret_inv['data']);
        }

        //更新单据
        $where = " pay_status='{$this->sell_record['pay_status']}' ";
        $status = $this->save_record($where);
        if ($status === FALSE) {
            //要事务回滚
            return $this->format_ret(-1, '', "取消付款保存异常");
        }

        return $this->format_ret(1);
    }

    //参数检查
    function check_params(&$params) {
        $status = 1;
        return $this->format_ret($status);
    }

    private function del_pay_money() {


        $where = array('record_code' => $this->sell_record['record_code']);
        $ret_del = $this->delete($where);
        if ($ret_del['status'] < 1) {
            //要事务回滚
            return $this->format_ret(-1, '', "取消报错异常");
        }

        $num = $this->affected_rows();
        if ($num < 0) {
            //要事务回滚
            return $this->format_ret(-1, '', "取消报错异常");
        }

        $this->new_sell_record['pay_status'] = 0;
        $this->new_sell_record['pay_time'] = '0000-00-00 00:00:00';
        $this->new_sell_record['buyer_real_amount'] = 0;

        return $this->format_ret(1);
    }

    function set_inv_record_info($inv_data) {
        $this->new_sell_record = array_merge($this->new_sell_record, $inv_data);
    }

    function set_opt_log($dsc = '') {

        $log_data['action_code'] = 'cancelpay';
        $log_data['action_name'] = '取消付款';
        $log_data['action_desc'] = '取消付款金额：' . $dsc;

        $this->set_log($log_data);
    }

}
