<?php

require_model('tb/TbModel');

/**
 * 成本月结单日志业务
 */
class CostMonthLogModel extends TbModel {

    protected $table = 'cost_month_log';
    //确认状态
    public $sure_status = array(
        0 => '未确认',
        1 => '已确认',
    );
    //审核状态
    public $check_status = array(
        0 => '未审核',
        1 => '已审核',
    );

    function get_by_page($filter) {
        $sql_main = " FROM {$this->table} r1 WHERE 1 ";
        if (isset($filter['record_code']) && !empty($filter['record_code'])) {
            $sql_main .= " AND record_code = :record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        }
        $sql_main .= " ORDER BY r1.action_time DESC";
        $select = " r1.* ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$action) {
            $action['status'] = $this->sure_status[$action['sure_status']] . '，' . $this->check_status[$action['check_status']];
        }
        return $this->format_ret(1, $data);
    }

    /**
     * 写入月结单日志
     * @param string $record_code
     * @param string $action_name
     * @param string $action_desc
     * @param bool $isDeamon
     */
    function add_log($record_code, $action_name, $action_desc = '', $isDeamon = false) {
        $sql = "SELECT is_sure, is_check FROM cost_month WHERE record_code = :record_code";
        $record = $this->db->get_row($sql, array('record_code' => $record_code));
        if (empty($record)) {
            return $this->format_ret(-1, '', '月结单不存在:' . $record_code);
        }

        $log = array();
        $log['record_code'] = $record_code;
        $log['sure_status'] = $record['is_sure'];
        $log['check_status'] = $record['is_check'];
        $log['action_name'] = $action_name;
        $log['action_desc'] = $action_desc;
        $log['action_time'] = date('Y-m-d h:i:s');

        if ($isDeamon == false && CTX()->app['mode'] == 'cli') {
            $isDeamon = true;
        }

        if ($isDeamon) {
            $log['user_code'] = load_model('sys/UserTaskModel')->get_user_code();
            $log['user_name'] = load_model('sys/UserTaskModel')->get_user_name();
            $log['user_code'] = !empty($log['user_code']) ? $log['user_code'] : '计划任务';
            $log['user_name'] = !empty($log['user_name']) ? $log['user_name'] : '计划任务';
        } else {
            $log['user_code'] = CTX()->get_session('user_code');
            $log['user_name'] = CTX()->get_session('user_name');
        }

        if (empty($log['user_code']) || empty($log['user_name'])) {
            return $this->format_ret(-1, '', '用户登录数据错误');
        }

        $ret = $this->insert($log);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '保存日志出错');
        }
    }

}
