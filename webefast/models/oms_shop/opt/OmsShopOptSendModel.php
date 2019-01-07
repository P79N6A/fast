<?php

require_model('oms_shop/opt/OmsShopOptAbs');

class OmsShopOptSendModel extends OmsShopOptAbs {

    function opt(&$params) {
        $this->params = &$params;
        $ret_check = $this->check_params($this->params);
        if ($ret_check['status'] < 1) {
            return $ret_check;
        }
        // 发货的 ，电商门店
        if ($this->sell_record['send_way'] == 0 || ($this->sell_record['record_type'] == 1 && $this->sell_record['send_way'] == 1)) { //
            $inv_params = array('inv_status' => 6);
            $this->opt_other('inv', $inv_params);
        }

        $this->new_sell_record['send_status'] = 1;
        $this->new_sell_record['send_time'] = date('Y-m-d H:i:s');
        $where = " send_status=0  AND cancel_status=0 ";
        $status = $this->save_record($where);
        if ($status === FALSE) {
            //要事务回滚
            return $this->format_ret(-1, '', "发货异常");
        }

        $this->set_opt_log(); //设置发货日志


        return $this->format_ret(1);
    }

    function check(&$sell_record, &$sell_record_detail) {
        $this->sell_record = &$sell_record;
        $this->sell_record_detail = &$sell_record_detail;


        if ($this->sell_record['cancel_status'] == 1) {
            return $this->format_ret(-1, '', '单据已经作废不能发货');
        }
        if ($this->sell_record['pay_status'] != 2) {
            return $this->format_ret(-1, '', '单据没有完成付款，不能发货');
        }
        if ($this->sell_record['pay_status'] != 2) {
            return $this->format_ret(-1, '', '单据没有完成付款，不能发货');
        }
        if ($this->sell_record['send_status'] == 1) {
            return $this->format_ret(-1, '', '单据已经发货不能重复发货');
        }

        return $this->format_ret(1);
    }

    function check_params(&$params) {

        $status = 1;
        $msg = '';
        if ($this->sell_record['send_way'] == 1) {
            $this->new_sell_record['send_status'] = 1;
            $this->new_sell_record['send_time'] = date('Y-m-d H:i:s');
        } else {
            if (!isset($params['express_code']) || empty($params['express_code'])) {
                $status = -1;
                $msg .= '请选择快递公司';
            } else {
                $this->new_sell_record['express_code'] = $params['express_code'];
            }

            if (!isset($params['express_no']) || empty($params['express_no'])) {
                $msg .= '快递单号不能为空';
                $status = -1;
            } else {
                $this->new_sell_record['express_no'] = $params['express_no'];
            }
        }



        return $this->format_ret($status, '', $msg);
    }

    protected function set_opt_log($dsc = '') {
        $log_data['action_code'] = 'send';
        $log_data['action_name'] = '发货';
        $log_data['action_desc'] = '发货快递单号：' . $this->new_sell_record['express_no'];

        $this->set_log($log_data);
    }

}
