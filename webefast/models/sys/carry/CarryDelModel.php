<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CarryMoveDel
 *
 * @author wq
 */
require_model('sys/carry/CarryBaseModel');

class CarryDelModel extends CarryBaseModel {

    protected $tb_config = array();
    protected $is_child = 0;
    protected $tb;
    protected $move_data = array();
    protected $code_data = array();
    private $task_data = array();
    private $child_data_num = 0;
    private $tb_key;
    private $parent_code = array();

    function exec(&$param) {
        $this->begin_trans();
        try {
            $is_init = $this->init_action($param);
            if ($is_init === false) {
                return $this->format_ret(-1, '', '任务状态异常');
            }
            $this->tb = $param['table'];


            if (isset($param['parent_task_code']) && !empty($param['parent_task_code'])) {
                if (isset($this->carry_conf[$param['p_table']]['move'][$param['table']])) {
                    $this->tb_config = $this->carry_conf[$param['p_table']]['move'][$param['table']];
                } else if (isset($this->carry_conf[$param['p_table']]['del'][$param['table']])) {
                    $this->tb_config = $this->carry_conf[$param['p_table']]['del'][$param['table']];
                } else if (isset($this->carry_conf[$param['p_table']]['index'][$param['table']])) {
                    $this->tb_config = $this->carry_conf[$param['p_table']]['index'][$param['table']];
                }

                $this->tb_key = isset($this->tb_config['key']) ? $this->tb_config['key'] : $this->carry_conf[$param['p_table']]['key'];
                $this->is_child = 1;

                $this->get_del_child_data();
                $this->del_child_data();
            } else {
                $this->tb_config = $this->carry_conf[$param['table']];
                $this->tb_config['condition'] = $this->tb_config['condition'][$param['condition_key']];
                $this->tb_key = isset($this->tb_config['key']) ? $this->tb_config['key'] : 'id';


                if (isset($this->tb_config['parent'])) {
                    $is_parent_over = $this->check_parent_is_over($this->tb_config['parent']);
                    if($is_parent_over===false){
                        //休眠2分钟
                        $this->rollback();
                        sleep(120);
                        return $this->format_ret(100);
                    }
                    
                    
                }


                if ($this->tb_config['type'] == 'del'&&!isset($this->tb_config['del'])) {
                    $this->del_data();
                } else {
                    $this->get_del_data();
                }
            }



            if ($this->is_child == 0) {
                if (!empty($this->code_data)) {
                    //创建子任务
                    $this->create_child_task();
                } else {
                    $this->update_status(6);
                    $this->check_task_is_over();
                }
            } else {
                $this->sys_carry_data($this->tb, $this->child_data_num, 'del');
                $this->update_status(7);
                $this->check_child_task();
            }
        } catch (Exception $ex) {
            $this->rollback();
            return $this->format_ret(-1, '', $ex->getMessage());
        }
        $this->commit();
        return $this->format_ret(1);
    }

    function get_del_data() {
        $key = &$this->tb_key;
        $sql = "select  {$key} from {$this->tb} t where " . $this->get_condition_where();
        $data = $this->db->get_all($sql);
        $this->code_data = array();
        foreach ($data as $val) {
            $this->code_data[] = $val[$key];
        }
        $this->update_task_data($this->code_data);
    }

    function del_data() {
        
        while (true){
        
            $sql = "delete from {$this->tb}  where " . $this->get_condition_where();
            $this->db->query($sql);
            $num = $this->db->affected_rows();
            if($num==0){
                break;
            }
        }
        
    }

    function get_del_child_data() {
        $key = &$this->tb_key;
        if (isset($this->tb_config['key_type'])) {
            $key = $this->tb_config['key'][1];
        }

        $data = $this->get_carry_task_data($this->param['parent_task_code'], $this->param['task_type']);
        $this->parent_code = $data;
        if (isset($this->tb_config['key_type'])) {
            $this->get_child_key_data($this->tb_config['key'], $data);
        }
        $this->code_data = &$data;
    }

    function get_condition_where() {
        $where = isset($this->tb_config['condition']['where']) ? $this->tb_config['condition']['where'] : ' 1=1  ';
        if ($this->is_child == 0) {
            $end_date = $this->end_date;
            if ($this->tb_config['condition']['type'] == 'datetime') {
                $end_date.=' 23:59:59';
            }
            $where .= " AND " . $this->tb_config['condition']['time_key'] . " <='{$end_date}' ";

            $limit = isset($this->param['page_size']) ? $this->param['page_size'] : 10000;

            $where.="  limit {$limit}  ";
        }
        return $where;
    }

    function get_child_key_data($key_arr, &$data) {
        $table = $this->param['p_table'];
        $conf = $this->carry_conf[$table];
        $p_key = $key_arr[0];

        $code_str = "'" . implode("','", $data) . "'";
        $sql = "select {$p_key} as code from {$table} where {$conf['key']} in ({$code_str}) ";
        $code_data = $this->db->get_all($sql);
        $new_data = array();
        foreach ($code_data as $val) {
            $arr = explode(',', $val['code']);
            foreach ($arr as $code) {
                $code = str_replace("'", '', $code); //处理特殊情况 交易号 带' 符号
                $new_data[] = trim($code);
            }
        }
        $data = $new_data;
    }

    function del_child_data() {

        $key = &$this->tb_key;
        if (isset($this->tb_config['key_type'])) {
            $key = $this->tb_config['key'][1];
        }
        $code_str = "'" . implode("','", $this->code_data) . "'";

        $sql = "delete from {$this->tb} where {$key} in({$code_str})";

        $this->db->query($sql);
    }

    function del_parent_data() {
        $key = $this->carry_conf[$this->param['p_table']]['key'];
        $code_str = "'" . implode("','", $this->parent_code) . "'";
        $sql = "delete from {$this->param['p_table']}  where {$key} in({$code_str})";
        $this->db->query($sql);
    }

    function create_child_task() {
        $default_row = array(
            'status' => 0,
            'sys_task_id' => 0,
            'parent_task_code' => $this->param['task_code'],
        );
        $task_param = array(
            'parent_task_code' => $this->param['task_code'],
            'p_table' => $this->tb,
            'task_sn' => $this->param['task_sn'],
        );
        foreach ($this->tb_config['move'] as $table => $conf) {
            $row = $default_row;
            $row['task_type'] = 'del';
            $row['task_code'] = $table;
            $task_param['table'] = $table;
            $row['task_param'] = json_encode($task_param);
            $this->task_data[] = $row;
        }
        foreach ($this->tb_config['del'] as $table => $conf) {
            $row = $default_row;
            $row['task_type'] = 'del';
            $row['task_code'] = $table;
            $task_param['table'] = $table;
            $row['task_param'] = json_encode($task_param);
            $this->task_data[] = $row;
        }

        if (isset($this->tb_config['index'])) {
            foreach ($this->tb_config['index'] as $table => $conf) {
                $row = $default_row;
                $row['task_type'] = 'del';
                $row['task_code'] = $table;
                $task_param['table'] = $table;
                $row['task_param'] = json_encode($task_param);
                $this->task_data[] = $row;
            }
        }



        load_model('sys/carry/SysCarryTaskModel')->create_task_more($this->task_data);
        $this->start_child_task($this->task_data[0]);
    }

    function start_child_task($row) {

        $this->start_task($row['task_code'], $row['task_type'], $row['parent_task_code']);
    }

    function check_child_task() {
        $sql = "select * from sys_carry_task where parent_task_code=:parent_task_code AND status=0 ";
        $sql_value = array(':parent_task_code' => $this->param['parent_task_code']);
        $data = $this->db->get_all($sql, $sql_value);
        if (empty($data)) {
            $sql = "select count(1) from sys_carry_task where parent_task_code=:parent_task_code AND status<>2 ";
            $sql_value = array(':parent_task_code' => $this->param['parent_task_code']);
            $num = $this->db->get_value($sql, $sql_value);
            if ($num == 0) {
                $this->del_parent_data();
                $this->start_task($this->param['parent_task_code'], 'del');
            }
        } else {
            $this->start_child_task($data[0]);
        }
    }

    //特殊增加
    function check_parent_is_over($parent_code) {

        $parent_conf = $this->carry_conf[$parent_code];
        $count = count($parent_conf['condition']);
        $parent_code_arr = array();
        for ($i = 0; $i <= $count; $i++) {
            $parent_code_arr[] = $parent_code . "," . $i;
        }
        $parent_code_str = "'" . implode("','", $parent_code_arr). "'" ;
        $sql = "select count(1) from sys_carry_task where status<>2 AND (  task_code in({$parent_code_str}) OR parent_task_code in({$parent_code_str}) )";

        $num = $this->db->get_value($sql);
        return $num > 0 ? false : true;
    }

}
