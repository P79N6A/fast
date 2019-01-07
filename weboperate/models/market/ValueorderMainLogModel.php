<?php

require_model('tb/TbModel');
require_lib("comm_util");

class ValueorderMainLogModel extends TbModel {

    /**
     * 表名
     */
    public function get_table() {
        return 'osp_valueorder_main_log';
    }

   
    public function log($user_code,$order_id, $order_code, $kh_id, $val_action,$val_status, $remark = '',$operator_type=1) {
        $ret=$this->insert(array(
            'order_id' => $order_id,
            'order_code' => $order_code,
            'kh_id' => $kh_id,
           // 'val_operator' => CTX()->get_session("user_code"),
            'val_operator' =>$user_code,
            'val_action' => $val_action,
            'val_status' => $val_status,
            'val_remark' => $remark,
            'val_time' => date('Y-m-d H:i:s'),
            'operator_type'=>$operator_type,
        ));
        return $ret;
    }

    public function getLogByPage($filter) {
        $sql_value=array();
        $sql_main = " FROM `{$this->table}` as r1 WHERE 1 ";
        if (isset($filter['order_id']) && $filter['order_id'] != '') {
            $sql_main.=' AND order_id=:order_id';
            $sql_value[':order_id'] = $filter['order_id'];
        } else {
            $sql_main.=' AND 1=2';
        }
        //前端和后端操作人员
        if (isset($filter['operator_type']) && $filter['operator_type'] != '') {
            $sql_main.=' AND operator_type=:operator_type';
            $sql_value[':operator_type'] = $filter['operator_type'];
        } else {
            $sql_main.=' AND 1=2';
        }
        $sql_main.=' ORDER BY val_time DESC';
        $select='r1.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, $select);
        return $this->format_ret(OP_SUCCESS, $data);
    }

}
