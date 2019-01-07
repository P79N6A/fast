<?php

require_model('mid/MidAbs');

class MidWbmReturnModel extends MidAbs {
    /*
     * 获取主单信息
     */

    function get_order_info($record_code) {

        $sql = "select * from wbm_return_record where record_code=:record_code ";
        $sql_values = array(
            ':record_code' => $record_code,
        );
        $data = $this->db->get_row($sql, $sql_values);
        if (empty($data)) {
            return $this->format_ret(-1, '', '未找到单据数据');
        }
        return $this->format_ret(1, $data);
    }

    /*
     * 获取明细信息
     */

    function get_order_detail($record_code) {
        $sql = "select * from wbm_return_record_detail where record_code=:record_code ";
        $sql_values = array(
            ':record_code' => $record_code,
        );
        $data = $this->db->get_all($sql, $sql_values);
        if (empty($data)) {
            return $this->format_ret(-1, '', '未找到单据数据');
        }
        return $this->format_ret(1, $data);
    }

    /*
     * 获取中间信息
     */

    function get_mid_data($record_code, $base_info = array()) {
        $ret = $this->get_order_info($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $mid_data = array(
            'record_code' => $ret['data']['record_code'],
//            'express_code' => $ret['data']['return_express_code'],
//            'express_no' => $ret['data']['return_express_no'],
            'efast_store_code' => $ret['data']['store_code'],
        );

        $mid_data = array_merge($base_info, $mid_data);
        return $this->format_ret(1, $mid_data);
    }

    function order_shipping(&$order_info) {

    
    }

}
