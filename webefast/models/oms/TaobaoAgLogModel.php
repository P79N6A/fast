<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');

class TaobaoAgLogModel extends TbModel {


    function get_table() {
        return 'api_taobao_ag_log';
    }

    /**
     * 列表查询
     * @param $filter
     * @return array
     */
    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} rl WHERE 1";
        $sql_values = array();
        //退单编号
        if (isset($filter['refund_id']) && $filter['refund_id'] != '') {
            $sql_main .= " AND rl.refund_id = :refund_id ";
            $sql_values[':refund_id'] = $filter['refund_id'];
        }
        $sql_main.=" ORDER BY id DESC";
        $select = 'rl.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }


}
