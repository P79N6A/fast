<?php

/**
 * 活动商品日志
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('crm');

class ActivityLogModel extends TbModel {

    function __construct() {
        parent::__construct('crm_goods_log', 'log_id');
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "inner join sys_user rr on rl.user_name=rr.user_code";
        $sql_main = "FROM {$this->table} rl " . $sql_join . " WHERE 1";
        $sql_values = array();
        //单据编号
        if (isset($filter['activity_code']) && $filter['activity_code'] != '') {
            $sql_main .= " AND (rl.activity_code = :activity_code )";
            $sql_values[':activity_code'] = $filter['activity_code'];
        }
        $select = 'rl.*,rr.user_name';
        $sql_main .= " order by log_id desc";
        //echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        /*
          filter_fk_name($data['data'], array('store_code|store', 'adjust_type|record_type'));
         */
        foreach ($data['data'] as &$val) {
            $val['user_code'] = $val['user_name'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /*
     * 添加日志
     */

    function insert($data) {
        return parent::insert($data);
    }

    function insert_multi($row_arr, $is_filter_repeat = false) {
        return parent::insert_multi($row_arr, $is_filter_repeat);
    }

}
