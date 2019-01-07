<?php

require_model('oms_shop/opt/OmsShopOptAbs');

class OmsShopOptCancelModel extends OmsShopOptAbs {

    function __construct() {
        parent::__construct();
    }

    function check(&$sell_record, &$sell_record_detail) {
        $this->sell_record = &$sell_record;
        $this->sell_record_detail = &$sell_record_detail;


        if ($this->sell_record['cancel_status'] == 1) {
            return $this->format_ret(-1, '', '单据已经作废不能重复作废');
        }
        if ($this->sell_record['send_status'] == 1) {
            return $this->format_ret(-1, '', '单据已经发货不能作废');
        }

        return $this->format_ret(1);
    }

    function opt(&$params) {
        $this->params = &$params;
        $ret_check = $this->check_params($this->params);
        if ($ret_check['status'] < 1) {
            return $ret_check;
        }


        //库存取消锁定
        if ($this->sell_record['pay_status'] == 2) {
            $inv_params = array('inv_status' => 0);
            $ret_inv = $this->opt_other('inv', $inv_params);
            if ($ret_inv['status'] < 1) {
                return $ret_inv;
            }
            $this->set_inv_record_info($ret_inv['data']);
        }

        //更新单据
        $this->new_sell_record['cancel_status'] = 1; //作废标识
        $this->new_sell_record['cancel_time'] = date('Y-m-d H:i:s');
        $where = " send_status=0 AND  pay_status='{$this->sell_record['pay_status']}' ";
        $status = $this->save_record($where);
        if ($status === FALSE) {
            //要事务回滚
            return $this->format_ret(-1, '', "取消付款保存异常");
        }

        $this->set_opt_log();

        return $this->format_ret(1);
    }

    //参数检查
    function check_params(&$params) {
        $status = 1;
        return $this->format_ret($status);
    }

    function set_inv_record_info($inv_data) {
        $this->new_sell_record = array_merge($this->new_sell_record, $inv_data);
    }

    function set_opt_log($dsc = '') {

        $log_data['action_code'] = 'cancel';
        $log_data['action_name'] = '单据作废';
        $log_data['action_desc'] = $dsc;

        $this->set_log($log_data);
    }

}
