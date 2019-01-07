<?php

/**
 * 结转主任务表
 *
 * @author wq
 */
require_model('tb/TbModel');

class SysCarryModel extends TbModel {

    private $state_arr = array(
        0 => '初始化中',
        1 => '结转准备中',
        2 => '正在结转数据',
        3 => '正在结转数据',
        4 => '对比结转数据',
        5 => '生存调整数据',
        6 => '删除数据',
        7 => '删除数据',
        8 => '结转结束',
    );

    function __construct() {
        parent::__construct('sys_carry');
    }

    /*
     * 创建结转任务
     */

    function create_carry($param) {

        list($year, $month) = explode("-", $param['end_time']);
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $param['end_time'].="-" . $days . " 23:59:59";

        $ret_check = $this->check($param);
        if ($ret_check['status'] < 1) {
            return $ret_check;
        }
        $this->begin_trans();
        
        $data['start_date'] = $this->get_start_time();
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['state'] = 0;
        $data['task_sn'] = $this->get_task_sn($param['end_time']);
        $data['end_date'] = $param['end_time'];
        $ret = $this->insert($data);
        
        load_model('sys/carry/SysCarryTaskModel')->clear_task();
        //创建数据初始化
        $task_param = array('task_sn' => $data['task_sn']);
        $task_data = array('task_type' => 'main', 'task_code' => 'init', 'task_param' => json_encode($task_param));
        load_model('sys/carry/SysCarryTaskModel')->create_task_more(array($task_data));
        $ret_task = load_model('sys/carry/CarryBaseModel')->start_task($task_data['task_code'], $task_data['task_type']);
        if ($ret_task['status'] < 1) {
            $this->rollback();
            return $ret_task;
        }


        $this->commit();

        return $ret;
    }

    function check(&$param) {
        $sql_check = "select count(1) from {$this->table} where state<>8 ";
        $num = $this->db->get_value($sql_check);
        if ($num > 0) {
            return $this->format_ret(-1, '', '不能创建结转任务，存在未完成任务');
        }
        list($year, $month) = explode("-", $param['end_time']);
        list($now_year,$now_month) = explode('-', date('Y-m'));
        if($year>$now_year){
             return $this->format_ret(-1, '', '结转月份太大');
        }
        if($year<=$now_year){
            $now_month = ($year<$now_year)?$now_month+12:$now_month;
           if($month>($now_month-1)){
                 return $this->format_ret(-1, '', '结转月份最小也一个月以前');
            }
        }
    
        
        
        
        return $this->format_ret(1);
    }

    function get_start_time() {

        $sql = "select max(end_date) from {$this->table}";
        $start_time = $this->db->get_value($sql);
        if (empty($start_time)) {
            $conf = load_model('sys/SysParamsModel')->get_val_by_code(array('online_date'));
            $start_time = $conf['online_date'];
        } else {
            $start_time = date('Y-m-d', strtotime($start_time) . ' +1 day');
        }
        return $start_time;
    }

    function get_start_date() {
        $sql = "select max(end_date) from {$this->table} where state=8";
        $start_date = $this->db->get_value($sql);
        if (!empty($start_date)) {
            $start_date = date('Y-m', strtotime($start_date));
        } else {
            $start_date = '';
        }
        return $start_date;
    }

    //获取任务编号
    function get_task_sn($endtime) {
        return date('Ymd', strtotime($endtime));
    }

    function get_by_page($filter) {
        $sql_main = " from sys_carry where 1 ";
        $select = "*";
        $sql_values = array();
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        foreach ($data['data'] as $val) {
            $val['state_name'] = $this->state_arr[$val['state']];
            $s_value = array(':task_sn' => $val['task_sn']);
            $c_data = $this->db->get_all("select * from sys_carry_data  where task_sn=:task_sn AND task_tb IN ('oms_sell_record','oms_sell_return')", $s_value);
            foreach ($c_data as $v) {
                if ($v['task_tb'] == 'oms_sell_record') {
                    $val['record_num'] = $v['sys_num'];
                } else {
                    $val['return_num'] = $v['sys_num'];
                }
            }
        }
        return $this->format_ret(1, $data);
    }

    function check_carry() {
        $sql = "select * from {$this->table} where state<8";
        $data = $this->db->get_row($sql);
        if (!empty($data)) {
            $data['state_name'] = $this->state_arr[$data['state']];
        }
        return $this->format_ret(1, $data);
    }

}
