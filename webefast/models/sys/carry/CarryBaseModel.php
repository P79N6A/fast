<?php

require_model('tb/TbModel');

class CarryBaseModel extends TbModel {

    protected $carry_conf = array();
    protected $task_sn = array();
    protected $end_date;
    protected $param;
    protected $run_num = 0;
    function __construct() {
        parent::__construct('sys_carry_task');
        $this->carry_conf = require_conf('sys/carry');
    }

    function init_action(&$param) {

            $sleep = $this->get_code_num($param['task_code'], 1000);
            $sleep = $sleep*1000;
            usleep($sleep);
   
        
        $this->task_sn = $param['task_sn'];
        $sql = "select * from sys_carry where task_sn=:task_sn";
        $data = $this->db->get_row($sql, array(':task_sn' => $this->task_sn));
        $this->end_date = $data['end_date'];
        $this->param = $param;

        
     
        $parent_task_code = isset($param['parent_task_code']) ? $param['parent_task_code'] : '';
        
        $sql_for ="SELECT * from sys_carry_task  where task_code=:task_code AND  task_type=:task_type AND parent_task_code=:parent_task_code FOR UPDATE ";
        $sql_value = array(':task_code'=>$param['task_code'],':task_type'=>$param['task_type'],':parent_task_code'=>$parent_task_code);
        $result = $this->db->get_all($sql_for,$sql_value);
        if(empty($result)){
            return FALSE;
        }
        
        $carry_task_data = $result[0];
        if($carry_task_data['status']!=0){
            return FALSE;
        }
        
        $where = " id='{$carry_task_data['id']}' ";
        $up_data['status'] = 1;
        $this->db->update('sys_carry_task', $up_data, $where);
        $status = $this->db->affected_rows();
        if ($status == 0) {
            return FALSE;
        }
        return TRUE;
    }

    function get_data_num($table, $key, $key_type, $where) {
        $end_date = $this->end_date;
        if ($key_type == 'datetime') {
            $end_date.=' 23:59:59';
        }
        $sql = "select count(1) from {$table} where {$where} AND {$key}<='{$end_date}' ";
        return $this->db->get_value($sql);
    }

    function get_child_data_num($table, $key, $code_arr) {
        $code_str = "'" . implode("','", $code_arr) . "'";
        $sql = "select count(1) from {$table} where {$key} in ({$code_str})";
        return $this->db->get_value($sql);
    }

    function sys_carry_data($tb, $num, $type) {
        $data = array();
        $data['task_sn'] = $this->task_sn;
        $data['task_tb'] = $tb;

        if ($type == 'move') {
            $data['sys_num'] = $num;
            $data['del_num'] = $num;
        } else {
            $data['sys_num'] = 0;
            $data['del_num'] = $num;
        }
        $update_str = " sys_num = VALUES(sys_num)+sys_num, del_num = VALUES(del_num)+del_num ";
        $this->insert_multi_duplicate('sys_carry_data', array($data), $update_str);
    }

    function save_move_num($tb, $num) {
        $where = " task_tb='{$tb}' AND task_sn='{$this->task_sn}' ";
        $data['move_num'] = $num;
        $this->update_exp('sys_carry_data', $data, $where);
    }

    function update_status($state) {
        $data['state'] = $state;
        $where = " task_sn = {$this->param['task_sn']} ";
        $this->db->update('sys_carry', $data, $where);

        $up_data['status'] = 2;
        $parent_task_code = isset($this->param['parent_task_code']) ? $this->param['parent_task_code'] : '';
        load_model('sys/carry/SysCarryTaskModel')->update_task($this->param['task_type'], $this->param['task_code'], $parent_task_code, $up_data);
    }

    function create_task($param = array(),$plan_over_time=0) {
        require_model('common/TaskModel');
        $task = new TaskModel();
        $task_data = array();
        $task_data['plan_over_time'] = $plan_over_time;
        $task_data['code'] = 'carry_' . $param['task_code'] . "_" . $param['task_type'];
        $param['app_act'] = 'sys/carry/action_task';
        $param['app_fmt'] = 'json';
        $task_data['start_time'] = time()+2;
        $task_data['request'] = $param;
        $task_data['error_num'] = 0;
        $ret = $task->save_task($task_data);
        if ($ret['status'] < 0) {
            if ($ret['status'] == -5) {
                $ret['message'] = "任务创建异常";
            }
        }
        return $ret;
    }

    function start_task($task_code, $task_type, $parent_task_code = '') {

        $sql = "select * from sys_carry_task where task_code=:task_code AND  task_type=:task_type AND parent_task_code=:parent_task_code";
        $row = $this->db->get_row($sql, array(':task_code' => $task_code, 'task_type' => $task_type, 'parent_task_code' => $parent_task_code));
        $task_param = json_decode($row['task_param'], true);

        $task_param['task_code'] = $task_code;
        $task_param['task_type'] = $task_type;
        $plan_over_time = 0;
        if($task_type=='index'||$task_type=='del'){
            $plan_over_time = time()+3600*10;//10个小时
        }
        $ret = $this->create_task($task_param,$plan_over_time);
        if ($ret['status'] > 0) {
            $up_data['sys_task_id'] = $ret['data'];
            $up_data['status'] = 0;
            $where = " task_code='{$task_code}' AND  task_type='{$task_type}' AND parent_task_code='{$parent_task_code}' ";
            $this->db->update('sys_carry_task', $up_data, $where);
        }else{
             if($this->run_num<100){
                sleep(2);
                $this->run_num++;
                return $this->start_task($task_code, $task_type, $parent_task_code);
             }
        }
        $this->run_num = 0;
        return $ret;
    }

    function get_carry_task_data($task_code, $task_type) {
        $sql = "select task_data from sys_carry_task where task_code=:task_code AND task_type=:task_type";
        $sql_value = array(':task_code' => $task_code, ':task_type' => $task_type);
        $data = $this->db->get_value($sql, $sql_value);
        $ret_data = array();
        if (!empty($data)) {
            $ret_data = json_decode($data, TRUE);
        }
        return $ret_data;
    }

    function update_task_data(&$code_data) {
        $param = $this->param;
        $param['page'] +=1;
        $up_data = array(
            'task_data' => json_encode($code_data),
            'task_param' => json_encode($param),
        );

        $parent_task_code = isset($this->param['parent_task_code']) ? $this->param['parent_task_code'] : '';
        $where = "task_code='{$this->param['task_code']}' AND task_type='{$this->param['task_type']}'  AND parent_task_code='{$parent_task_code}'";
        $this->db->update('sys_carry_task', $up_data, $where);
    }

    function get_code_num($code, $by_num = 100) {

        if (!preg_match("/^\d*$/", $code)) {
            $new_code = 0;
            for ($i = 0; $i < strlen($code); $i++) {
                $new_code+=ord($code[$i]);
            }
            $code = $new_code;
        }
        $len = strlen($code);
        if ($len > 8) {
            $start = $len - 8;
            $code = substr($code, $start);
        }

        $num = (int) ((int) $code % $by_num);
        return $num;
    }

    function check_task($task_type) {
        $sql = "select * from sys_carry_task where task_type=:task_type  AND parent_task_code='' AND status<>2";
        $sql_value = array(
            ':task_type' => $task_type,
        );
        $status = false;
        $data = $this->db->get_all($sql, $sql_value);
        $action_type = array(
            'main' => 'move',
            'move' => 'compare',
            'compare' => 'adjust',
            'adjust' => 'del',
        );
        if (empty($data)) {
            $new_type = $action_type[$task_type];
            $sql = "select * from sys_carry_task where task_type=:task_type";
            $sql_value = array(
                ':task_type' => $new_type,
            );
            $data = $this->db->get_all($sql, $sql_value);
            foreach ($data as $val) {
                $this->start_task($val['task_code'], $val['task_type'], $val['parent_task_code']);
            }
            $status = TRUE;
        }
        return $status;
    }

    function update_msg($msg) {
        $data['msg'] = $msg;
        $data['status'] = 10;
        $where = " task_sn = {$this->param['task_sn']} ";
        $this->db->update('sys_carry', $data, $where);
    }

    function check_task_is_over() {
        $task_type_arr = array('del', 'index');
        $task_type_str = "'" . implode("','", $task_type_arr) . "'";
        $sql = "select * from sys_carry_task where task_type in({$task_type_str})  AND parent_task_code='' AND status<>2";
        $data = $this->db->get_all($sql);
        if (empty($data)) {
            $data['state'] = 8;
            $where = " task_sn = {$this->param['task_sn']} ";
            $this->db->update('sys_carry', $data, $where);
        }
    }

    function start_index_start() {
        $sql = "select * from sys_carry_task where task_type=:task_type";
        $sql_value = array(
            ':task_type' => 'index',
        );
        $data = $this->db->get_all($sql, $sql_value);
        foreach ($data as $val) {
            $this->start_task($val['task_code'], $val['task_type'], $val['parent_task_code']);
        }
    }

}
