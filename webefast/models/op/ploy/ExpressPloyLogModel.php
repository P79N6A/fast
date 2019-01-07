<?php

require_model('tb/TbModel');

/**
 * 快递策略业务日志
 * @author WMH
 */
class ExpressPloyLogModel extends TbModel {

    protected $table = 'op_express_ploy_log';

    /**
     * 获取快递策略日志
     * @param array $filter 筛选条件
     * @return array 结果集
     */
    public function get_log_by_page($filter) {
        $sql_main = "FROM `{$this->table}` AS pl WHERE 1";
        $sql_values = array();
        $select = 'pl.`user_code`,pl.`user_name`,pl.`action_name`,pl.`action_time`,pl.`action_desc`';
        //快递策略编码
        if (isset($filter['ploy_code']) && $filter['ploy_code'] != '') {
            $sql_main .= ' AND pl.`ploy_code`=:ploy_code ';
            $sql_values[':ploy_code'] = $filter['ploy_code'];
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
     * 添加快递策略日志
     * @param string $ploy_code 快递策略编码
     * @param string $action_name 操作名称
     * @param string $action_desc 操作描述
     * @return array 结果
     */
    public function insert_log($ploy_code, $action_name, $action_desc = '') {
        $data = array(
            'ploy_code' => $ploy_code,
            'user_code' => CTX()->get_session('user_code'),
            'user_name' => CTX()->get_session('user_name'),
            'action_name' => $action_name,
            'action_desc' => $action_desc,
            'action_time' => time(),
        );

        return $this->insert_exp($this->table, $data);
    }

}
