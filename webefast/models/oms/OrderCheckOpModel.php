<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OrderCheckOpModel
 *
 * @author wq
 */
require_model('tb/TbModel');

class OrderCheckOpModel extends TbModel {

    private $strategy_detail_data = array();
    private $sell_record;
    private $record_detail;

    function get_table() {
        return 'order_check_strategy';
    }

    function check_order(&$record, &$record_detail) {
        $this->sell_record = &$record;
        $this->record_detail = &$record_detail;

        $this->init_strategy_detail_data();
        $check_arr = array();
        foreach ($this->strategy_detail_data as $check_strategy_code => $strategy_detail) {
            $fun = "check_" . $check_strategy_code;
            $is_check = $this->$fun($strategy_detail);
            if ($is_check == false) {
                $check_arr[] = $check_strategy_code;
            }
        }
        return $check_arr;
    }

    private function check_not_auto_confirm_with_goods(&$strategy_detail) {

        $is_check = TRUE;
        foreach ($this->record_detail as $detail) {
            if (in_array($detail['sku'], $strategy_detail)) {
                $is_check = FALSE;
                break;
            }
        }
        return $is_check;
    }

    private function check_not_auto_confirm_with_shop(&$strategy_detail) {

        $is_check = TRUE;
        if (in_array($this->sell_record['shop_code'], $strategy_detail)) {
            $is_check = FALSE;
        }
        return $is_check;
    }

    private function check_not_auto_confirm_with_store(&$strategy_detail) {
        $is_check = TRUE;
        if (in_array($this->sell_record['store_code'], $strategy_detail)) {
            $is_check = FALSE;
        }
        return $is_check;
    }

    private function check_not_auto_confirm_with_money(&$strategy_detail) {
        $is_check = TRUE;
        if (empty($strategy_detail[0])) {
            return $is_check;
        }
        $money_arr = explode(',', $strategy_detail[0]);
        $min_money = $money_arr[0];
        $max_money = $money_arr[1];
        if ($min_money !== '' && $this->sell_record['payable_money'] <= $min_money) {
            $is_check = FALSE;
        } else if ($max_money !== '' && $this->sell_record['payable_money'] > $max_money) {
            $is_check = FALSE;
        }

        return $is_check;
    }

    private function check_protect_time(&$strategy_detail) {
        $protect_time = (int) $strategy_detail[0];
        $record_lastchanged = strtotime($this->sell_record['lastchanged']) + $protect_time*60;
     //   $this->sell_record['lastchanged'];
        $time = time();
        $is_check = FALSE;
        if ($time > $record_lastchanged) { //解锁
            $is_check = TRUE;
        }
        return $is_check;
    }

    private function check_auto_confirm_time(&$strategy_detail) {
        return TRUE;
    }
    private function set_strategy_detail($check_strategy_code) {

        $sql = "select content from order_check_strategy_detail where check_strategy_code=:check_strategy_code ";
        $data = $this->db->get_all($sql,array(":check_strategy_code" => $check_strategy_code));
        foreach ($data as $val) {
            $this->strategy_detail_data[$check_strategy_code][] = $val['content'];
        }
    }

    private function init_strategy_detail_data() {
        if (empty($this->strategy_detail_data)) {
            $ret = $this->get_all(array('is_active' => 1));
            foreach ($ret['data'] as $val) {
                $this->set_strategy_detail($val['check_strategy_code']);
            }
        }
    }


}
