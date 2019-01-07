<?php

require_model('tb/TbModel');
require_model('prm/InvLogModel');

class InvAjustModel extends TbModel {

    function __construct() {
        parent::__construct();
    }

    /*
     * 调整单据批次锁定库存
     * $record_data record_code,record_type,store_code
     */

    function adjust_record_lock_lof_inv($record_data, $lof_data) {
        $ret_check = $this->check_adjust_record_lock_lof_inv($record_data, $lof_data);
        if ($ret_check['status'] < 0) {
            return $ret_check;
        }
       return $this->set_adjust_lock($record_data, $lof_data);
    }

    private function set_adjust_lock($record_data, $lof_data) {
        $sql_arr = array();
        $detail_data = array();
        $check_num = 0;
        foreach ($lof_data as $lof_row) {
            $sql_arr[] = $this->get_update_lof_record_sql($record_data, $lof_row);
            $sql_arr[] = "update goods_inv_lof set lock_num={$lof_row['num']} where store_code='{$record_data['store_code']}'  AND sku='{$lof_row['sku']}' AND  lof_no='{$lof_row['lof_no']}' ";
            $lof_row['store_code'] = $record_data['store_code'];
            $lof_row['occupy_type'] = 1;
            $check_num += $lof_row['num'];
            $detail_data[] = $lof_row;
        }
        
        if($check_num!=0){
               return $this->format_ret(-1, '', "单据批次调整数量不匹配");
        }
        
        
        $lof_log = new InvLogModel();
        $record_time = date('Y-m-d H:i:s');
        $log_info = array('relation_code' => $record_data['record_code'], 'relation_type' => $record_data['type'], 'occupy_type' => 1, 'record_time' => $record_time, 'remark' => '单据锁定批次库存调整');
        $lof_log->init($log_info, $detail_data);
        foreach ($sql_arr as $sql) {
            $this->db->query($sql);
        }

        $this->clear_record_data($record_data);

        $lof_log->set_log();
        
        return $this->format_ret(1);
    }


    private function get_update_lof_record_sql(&$record_data, $lof_row) {

        $record_type_arr = array('oms', 'oms_return');

        $sql = ""; // 1订单 2退货单 3换货单
        if (in_array($record_data['record_type'], $record_type_arr)) {
            $order_type = ($record_data['record_type'] == 'oms') ? 1 : 2;
            $sql = "update b2b_lof_datail set num={$lof_row['num']} where  order_code='{$record_data['record_code']}'  AND  order_type='{$order_type}'";
        } else {
            $sql = "update oms_sell_record_lof set num={$lof_row['num']} where record_code='{$record_data['record_code']}'  AND  record_type='{$record_data['record_type']}'";
        }
        $sql .="  AND sku='{$lof_row['sku']}' AND  lof_no='{$lof_row['lof_no']}'  ";
        return $sql;
    }

    private function clear_record_data(&$record_data) {
        $record_type_arr = array('oms', 'oms_return');
        if (in_array($record_data['record_type'], $record_type_arr)) {
            $order_type = ($record_data['record_type'] == 'oms') ? 1 : 2;
            $sql = "delete from b2b_lof_datail  where  order_code='{$record_data['record_code']}'  AND  order_type='{$order_type}' AND num=0";
        } else {
            $sql = "delete from oms_sell_record_lof  where record_code='{$record_data['record_code']}'  AND  record_type='{$record_data['record_type']}' AND num=0 ";
        }
        $this->db->query($sql);
    }

    private function check_adjust_record_lock_lof_inv(&$record_data, &$lof_data) {
        $ret_check_record = $this->check_record_data($record_data);
        if ($ret_check_record['status'] < 0) {
            return $ret_check_record;
        }
        $ret_check_lof = $this->check_lof_data($lof_data);
        return $ret_check_lof;
    }

    private function check_record_data(&$record_data) {

        $check_key = array('record_code' => '单据编号', 'record_type' => '单据类型', 'store_code' => '仓库');
        $no_find = $this->check_array_by_key($record_data, $check_key);
        $ret = empty($no_find) ? $this->format_ret(-1, '', "未找到" . implode(",", $no_find)) : $this->format_ret(1);
        return $ret;
    }

    private function check_lof_data(&$lof_data) {

        $check_key = array('sku' => 'sku', 'num' => '数量', 'lof_no' => '批次编号', 'production_date' => '生产日期');
        foreach ($lof_data as $val) {
            $no_find = $this->check_array_by_key($val, $check_key);
            if (!empty($no_find)) {
                return $this->format_ret(-1, '', "未找到" . implode(",", $no_find));
            }
        }
        return $this->format_ret(1);
    }

    private function check_array_by_key(&$array, &$key_arr) {
        $no_find = array();
        foreach ($key_arr as $key => $name) {
            if (!isset($array[$key])) {
                $no_find[] = $name;
            }
        }
        return $no_find;
    }

}
