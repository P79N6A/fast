<?php

require_model('tb/TbModel');

/**
 * 预售业务日志
 * @author WMH
 */
class PresellLogModel extends TbModel {

    private $sync_log_table = 'op_presell_sync_log';
    private $opt_log_table = 'op_presell_log';

    /**
     * 获取预售日志
     * @param array $filter 筛选条件
     * @return array 结果集
     */
    public function get_log_by_page($filter) {
        $sql_main = "FROM `{$this->opt_log_table}` AS pl WHERE 1";
        $sql_values = array();
        $select = 'pl.`user_code`,pl.`user_name`,pl.`action_name`,pl.`action_time`,pl.`action_desc`';
        //计划编码
        if (isset($filter['plan_code']) && $filter['plan_code'] != '') {
            $sql_main .= ' AND pl.`plan_code`=:plan_code ';
            $sql_values[':plan_code'] = $filter['plan_code'];
        } else {
            return array();
        }

        $sql_main .= ' ORDER BY pl.`action_time` DESC';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$row) {
            $row['action_time'] = empty($row['action_time']) ? '' : date('Y-m-d H:i:s', $row['action_time']);
        }

        $ret_data = $data;

        return $this->format_ret(1, $ret_data);
    }

    /**
     * 获取预售库存同步日志
     * @param array $filter 筛选条件
     * @return array 结果集
     */
    public function get_sync_log_by_page($filter) {
        $sql_main = "FROM `{$this->sync_log_table}` AS sl WHERE 1";
        $sql_values = array();
        $select = 'sl.`user_code`,sl.`user_name`,sl.`plan_code`,sl.`shop_code`,sl.`barcode`,sl.`sku_id`,sl.`num`,sl.`result`,sl.`insert_time`';
        //计划编码
        if (isset($filter['plan_code']) && $filter['plan_code'] != '') {
            $sql_main .= ' AND sl.`plan_code`=:plan_code ';
            $sql_values[':plan_code'] = $filter['plan_code'];
        } else {
            return array();
        }
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= ' AND sl.`barcode`=:barcode ';
            $sql_values[':barcode'] = $filter['barcode'];
        }
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= ' AND sl.`barcode`=:barcode ';
            $sql_values[':barcode'] = $filter['barcode'];
        }

        $sql_main .= ' ORDER BY sl.`insert_time` DESC';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$row) {
            $row['insert_time'] = empty($row['insert_time']) ? '' : date('Y-m-d H:i:s', $row['insert_time']);
        }
        filter_fk_name($data['data'], array('shop_code|shop'));

        $ret_data = $data;

        return $this->format_ret(1, $ret_data);
    }

    /**
     * 添加预售日志
     * @param string $plan_code 预售计划编码
     * @param string $action_name 操作名称
     * @param string $action_desc 操作描述
     * @return array 结果
     */
    public function insert_log($plan_code, $action_name, $action_desc = '') {
        $data = array(
            'plan_code' => $plan_code,
            'user_code' => CTX()->get_session('user_code'),
            'user_name' => CTX()->get_session('user_name'),
            'action_name' => $action_name,
            'action_desc' => $action_desc,
            'action_time' => time(),
        );

        return $this->insert_exp($this->opt_log_table, $data);
    }

    /**
     * 添加预售库存同步日志
     * @param array $data 日志数据
     * @return array 结果
     */
    public function insert_sync_log($data, $is_auto = 0) {
        if ($is_auto != 0) {
            $user_code = 'auto_task';
            $user_name = '计划任务';
        } else {
            $user_code = CTX()->get_session('user_code');
            $user_name = CTX()->get_session('user_name');
        }
        $insert_time = time();
        foreach ($data as &$val) {
            $val['user_code'] = $user_code;
            $val['user_name'] = $user_name;
            $val['insert_time'] = $insert_time;
        }
        return $this->insert_multi_exp($this->sync_log_table, $data);
    }

}
