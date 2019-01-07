<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_model('tb/TbModel');
require_model('sys/carry/CarryBaseModel');

class CarryInitModel extends CarryBaseModel {

    private $task_data = array();
    private $page_size = 10000;

    function init(&$param) {
        $is_init = $this->init_action($param);
        if ($is_init === false) {
            return $this->format_ret(1);
        }

        //初始化表
        $kh_id = CTX()->saas->get_saas_key();
        $task_sn = $this->task_sn;
        load_model('sys/CarryDataModel')->check_is_init_tb($kh_id, $task_sn);

        //初始化任务

        foreach ($this->carry_conf as $table => $val) {
            $this->init_data($table, $val);
        }

        $this->init_other_data();

        $this->init_task();

        $this->update_status(1);
        $this->check_task('main');
        return $this->format_ret(1);
    }

    private function init_other_data() {
        $default_row = array(
            'status' => 0,
            'sys_task_id' => 0,
        );
        $task_param = array(
            'task_sn' => $this->param['task_sn'],
        );
        $arr = array('compare', 'adjust');
        foreach ($arr as $val) {
            $row = $default_row;
            $row['task_param'] = json_encode($task_param);
            $row['task_type'] = $val;
            $row['task_code'] = $val;
            $this->task_data[] = $row;
        }

        //索引
        $conf = require_conf('carry_index');
        foreach ($conf as $tb => $val) {
            $row = $default_row;
            $row['task_param'] = json_encode($task_param);
            $row['task_type'] = 'index';
            $row['task_code'] = $tb;
            $this->task_data[] = $row;
        }
        $sql = "TRUNCATE api_deal_code;";
         $this->db->query($sql);
    }

    private function init_data($table, &$conf) {
        $default_row = array(
            'task_type' => $conf['type'],
            'status' => 0,
            'sys_task_id' => 0,
        );
        $task_param = array(
            'table' => $table,
            'task_sn' => $this->param['task_sn'],
        );
        if(!isset($conf['condition'])){
            return false;
        }
        
        foreach ($conf['condition'] as $k => $condition) {
            $row = $default_row;
            $task_param['condition_key'] = $k;
            $num = $this->set_init_num($table, $condition, $conf['type']);
            $task_param['count'] = $num;
            $task_param['page'] = 1;
            $task_param['page_num'] = ceil($num / $this->page_size);
            $task_param['page_size'] = $this->page_size;
            $row['task_param'] = json_encode($task_param);
            $row['task_code'] = $table . "," . $k;
            $this->task_data[] = $row;

            if ($conf['type'] == 'move') {
                $new_row = $row;
                $new_row['task_type'] = 'del';
                $this->task_data[] = $new_row;
            }
        }
        return true;
        //var_dump( $this->task_data);die;
    }

    private function init_task() {
        load_model('sys/carry/SysCarryTaskModel')->create_task_more($this->task_data);
    }

    function set_init_num($table, $condition, $type) {

        $num = $this->get_data_num($table, $condition['time_key'], $condition['type'], $condition['where']);
        //   var_dump('dd',$num,$table, $condition['time_key'], $condition['type'], $condition['where']);
        $this->sys_carry_data($table, $num, $type);
        return $num;
    }

}
