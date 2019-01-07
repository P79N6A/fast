<?php

require_model('tb/TbModel');

/**
 * 收款统计报表
 */
class CollectionStatisticModel extends TbModel {

    function get_table() {
        return 'wbm_store_out_record';
    }

    /**组装sql
     * @param $filter
     * @param bool $onlySql
     * @return array
     */
    function get_sql_by_page($filter, $onlySql = false) {
        $sql_values = array();
        $sql_main = " FROM {$this->table} rl WHERE 1 AND rl.pay_status<>0 AND rl.is_store_out=1 AND rl.is_cancel<>1 ";

        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] !== '') {
            $sql_main .= " AND rl.record_code= :record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        }
        //付款状态
        if (isset($filter['pay_status']) && $filter['pay_status'] !== '') {
            $sql_main .= " AND rl.pay_status= :pay_status ";
            $sql_values[':pay_status'] = $filter['pay_status'];
        }
        //分销商代码
        if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
            $distributor_str = deal_strs_with_quote($filter['custom_code']);
            $sql_main .= " AND rl.distributor_code IN({$distributor_str})";
        }
        if ($onlySql) {
            $sql = array('from' => $sql_main, 'params' => $sql_values);
            return array('status' => '1', 'data' => $sql, 'message' => '仅返回SQL');
        }

        return array(
            'filter' => $filter,
            'sql_main' => $sql_main,
            'sql_values' => $sql_values,
        );
    }

    /**列表查询
     * @param $filter
     * @return array
     */
    function get_list_by_page($filter) {
        $ret = $this->get_sql_by_page($filter);
        $sql_main = $ret['sql_main'];
        $sql_values = $ret['sql_values'];
        $select = " SELECT DATE_FORMAT(rl.is_store_out_time,'%Y-%m') AS store_out_months,rl.distributor_code AS custom_code,rl.money,rl.pay_money,rl.express_money ";

        $select_main=$select.$sql_main;
        $sql=" FROM (".$select_main.") AS t WHERE 1 ";
        //出库时间
        if (isset($filter['store_out_time_start']) && $filter['store_out_time_start'] != '') {
            $sql .= " AND (t.store_out_months >= :store_out_time_start )";
            $sql_values[':store_out_time_start'] = $filter['store_out_time_start'];
        }
        if (isset($filter['store_out_time_end']) && $filter['store_out_time_end'] != '') {
            $sql .= " AND (t.store_out_months <= :store_out_time_end )";
            $sql_values[':store_out_time_end'] = $filter['store_out_time_end'];
        }
        $select_end = "t.store_out_months,t.custom_code,sum(t.money) AS sum_money,sum(t.pay_money) AS sum_pay_money,sum(t.express_money) AS sum_express_money";
        $sql .= " GROUP BY t.store_out_months,t.custom_code";
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select_end, true);
        foreach ($data['data'] as &$val) {
            $val['sum_money'] = sprintf("%.2f", $val['sum_money']);
            $val['sum_pay_money'] = sprintf("%.2f", $val['sum_pay_money']);
            $val['sum_pending_money'] = sprintf("%.2f", $val['sum_money'] - $val['sum_pay_money']);
            $val['goods_money'] = sprintf("%.2f", $val['sum_money'] - $val['sum_express_money']);
        }
        filter_fk_name($data['data'], array('custom_code|custom'));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**汇总统计
     * @param $filter
     * @return array|bool|mixed
     */
    function report_count($filter) {
        // 汇总
        $sqlArr = $this->get_sql_by_page($filter, true);
        $sqlArr = $sqlArr['data'];
        $sql_main = $sqlArr['from'];
        $sql_main = " SELECT DATE_FORMAT(rl.is_store_out_time,'%Y-%m') AS store_out_months,rl.money,rl.pay_money,rl.express_money " . $sql_main;
        $sql = " SELECT sum(t.money) AS money_all,sum(t.pay_money) AS pay_money_all,sum(t.express_money) AS express_money_all FROM(" . $sql_main . ") AS t WHERE 1";
        //出库时间
        if (isset($filter['store_out_time_start']) && $filter['store_out_time_start'] != '') {
            $sql .= " AND (t.store_out_months >= :store_out_time_start )";
            $sqlArr['params'][':store_out_time_start'] = $filter['store_out_time_start'];
        }
        if (isset($filter['store_out_time_end']) && $filter['store_out_time_end'] != '') {
            $sql .= " AND (t.store_out_months <= :store_out_time_end )";
            $sqlArr['params'][':store_out_time_end'] = $filter['store_out_time_end'];
        }
        $row = $this->db->get_row($sql, $sqlArr['params']);
        $row['pending_money_all'] = sprintf("%.2f", $row['money_all'] - $row['pay_money_all']);
        $row['goods_money_all'] = sprintf("%.2f", $row['money_all'] - $row['express_money_all']);

        return $row;
    }

    /**收款统计明细
     * @param $filter
     * @return array
     */
    function get_detail_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl WHERE 1 AND rl.pay_status<>0 AND rl.is_store_out=1 AND rl.is_cancel<>1 ";
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] !== '') {
            $sql_main .= " AND rl.record_code= :record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        }
        //付款状态
        if (isset($filter['pay_status']) && $filter['pay_status'] !== '') {
            $sql_main .= " AND rl.pay_status= :pay_status ";
            $sql_values[':pay_status'] = $filter['pay_status'];
        }
        //分销商代码
        if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
            $distributor_str = deal_strs_with_quote($filter['custom_code']);
            $sql_main .= " AND rl.distributor_code IN({$distributor_str})";
        }
        //出库月份
        if (isset($filter['store_out_months']) && $filter['store_out_months'] != '') {
            $sql_main .= " AND DATE_FORMAT(rl.is_store_out_time,'%Y-%m')= :store_out_months";
            $sql_values[':store_out_months'] = $filter['store_out_months'];
        }
        $select = " pay_status,rl.record_code,rl.is_store_out_time,distributor_code AS custom_code, money, pay_money,express_money";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, false);
        foreach ($data['data'] as &$val) {
            $val['money'] = sprintf("%.2f", $val['money']);
            $val['pay_money'] = sprintf("%.2f", $val['pay_money']);
            $val['pending_money'] = sprintf("%.2f", $val['money'] - $val['pay_money']);
            $val['goods_money'] = sprintf("%.2f", $val['money'] - $val['express_money']);
        }
        filter_fk_name($data['data'], array('custom_code|custom'));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

}
