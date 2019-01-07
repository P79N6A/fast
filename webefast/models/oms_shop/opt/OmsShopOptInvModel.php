<?php

require_model('oms_shop/opt/OmsShopOptAbs');

class OmsShopOptInvModel extends OmsShopOptAbs {

    function check(&$sell_record, &$sell_record_detail) {
        $this->sell_record = &$sell_record;
        $this->sell_record_detail = &$sell_record_detail;
        return $this->format_ret(1);
    }

    function opt(&$params) {

        $this->params = &$params;
        $ret_check = $this->check_params($this->params);
        if ($ret_check['status'] < 1) {
            return $ret_check;
        }

        require_model('prm/InvOpModel');
        $record_detail = $this->get_opt_inv_detail($this->params['inv_status']);
        $inv = new InvOpModel($this->sell_record['record_code'], 'oms_shop', $this->sell_record['send_store_code'], $this->params['inv_status'], $record_detail);
        if ($this->params['inv_status'] == 6) { //门店收银端发货，强制发货
            $inv->lock_allow_negative_inv(1);
            $inv->force_negative_inv();
        }

        $ret = $inv->adjust();
        if ($ret['status'] > 0) {
            $this->set_lock_detail($this->params['inv_status'], $ret['data']);
        }

        $this->set_opt_log();
        $ret['data'] = $this->new_sell_record;
        return $ret;
    }

    function get_opt_inv_detail($inv_status) {
        $record_detail = array();
        //取消锁定和扣减库存
        if ($inv_status == 0 || $inv_status == 2) { //$record_code
            $record_detail = load_model('oms_shop/OmsShopOptModel')->get_record_detail_lof($this->sell_record['record_code']);
        } else {
            $record_detail = $this->sell_record_detail;
        }
        return $record_detail;
    }

    function set_lock_detail($inv_status, &$lock_detail) {

        if ($inv_status == 1 || $inv_status == 6) {

            foreach ($lock_detail as $val) {
                $where = "record_code = '{$this->sell_record['record_code']}' AND sku='{$val['sku']}'";
                $up_data = array('lock_num' => $val['lock_num']);
                $this->update_exp("oms_shop_sell_record_detail", $up_data, $where);
            }
        } else if ($inv_status == 0) {
            $up_data = array('lock_num' => 0);
            $where = "record_code = '{$this->sell_record['record_code']}'";
            $this->update_exp("oms_shop_sell_record_detail", $up_data, $where);
        }
        $this->set_lock_status();
    }

    function check_params(&$params) {
        $inv_status_arr = array(0, 1, 2, 6);
        if (!isset($params['inv_status']) && $inv_status_arr) {
            return $this->format_ret(-1, '', '单据库存处理类型异常');
        }
        return $this->format_ret(1);
    }

    private function set_lock_status() {
        $sql = "select sum(lock_num) as lock_num,sum(num) as num from oms_shop_sell_record_detail where record_code =:record_code";
        $sql_value = array(':record_code' => $this->sell_record['record_code']);
        $num_arr = $this->db->get_row($sql, $sql_value);
        $lock_inv_status = 0;
        if ($num_arr['num'] == $num_arr['lock_num']) {
            $lock_inv_status = 2;
        } else if ($num_arr['num'] > $num_arr['lock_num'] && $num_arr['lock_num'] > 0) {
            $lock_inv_status = 1;
        }
        $this->new_sell_record['lock_inv_status'] = $lock_inv_status;
    }

    function set_opt_log($dsc = '') {
        $opt_log = array(
            '0' => array('action_code' => 'cancel_lock', 'action_name' => '取消锁定'),
            '1' => array('action_code' => 'lock', 'action_name' => '锁定库存'),
            '2' => array('action_code' => 'lock_reduce', 'action_name' => '扣减库存'),
            '6' => array('action_code' => 'lock_shop_reduce', 'action_name' => '门店扣减库存'),
        );

        $log_data = $opt_log[$this->params['inv_status']];


        $this->set_log($log_data);
    }

}
