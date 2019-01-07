<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CarryMoveModel
 *
 * @author wq
 */
require_model('tb/TbModel');
require_model('sys/carry/CarryBaseModel');

class CarryMoveModel extends CarryBaseModel {

    protected $tb_config = array();
    protected $is_child = 0;
    protected $is_index = 0;
    protected $tb;
    protected $move_data = array();
    protected $code_data = array();
    private $task_data = array();
    private $child_data_num = 0;
    private $max_id = 0;
    private $tb_key;

    function exec(&$param) {
        $this->begin_trans();
        try{
        $is_init = $this->init_action($param);
        if ($is_init === false) {
            return $this->format_ret(-1,'','任务状态异常');
        }
        $this->move_data = array();
        
        $this->tb = $param['table'];
        if (isset($param['parent_task_code']) && !empty($param['parent_task_code'])) {
            $this->is_child = 1;

            if (isset($param['is_index'])) {

                $this->tb_config = $this->carry_conf[$param['p_table']]['index'][$param['table']];
                $this->tb_key = isset($this->tb_config['key']) ? $this->tb_config['key'] : $this->carry_conf[$param['p_table']];
                $this->is_index = 1;
                $this->get_index_move_data();
            } else {
                $this->tb_config = $this->carry_conf[$param['p_table']]['move'][$param['table']];
                $this->tb_key = isset($this->tb_config['key']) ? $this->tb_config['key'] : $this->carry_conf[$param['p_table']]['key'];
                $this->get_child_move_data();
            }
        } else {
            $this->tb_config = $this->carry_conf[$param['table']];
            $this->tb_key = $this->tb_config['key'];

            $this->tb_config['condition'] = $this->tb_config['condition'][$param['condition_key']];
            $this->get_move_data();
        }


        $this->save_move_data();

        if ($this->is_child == 0) {

            $this->param['max_id'] = $this->max_id;
            $this->update_task_data($this->code_data);

            if (!empty($this->move_data)) {
                //创建子任务
                $this->create_child_task();
            } else {
                $this->update_status(2);
                $this->check_task('move');
            }
        } else {
            $this->sys_carry_data($this->tb, $this->child_data_num, 'move');
            //检查子任务是否全部结束，全部结束，检查主任务，是否要执行下一个流程
            $this->update_status(3);
            $this->check_child_task();
        }
        }  catch (Exception $ex){
                   $this->rollback();
                  return $this->format_ret(-1,'',$ex->getMessage());
        }
        $this->commit();
        return $this->format_ret(1);
    }

    function save_move_data() {
        if (!empty($this->move_data)) {
            
            $tb = ($this->is_index==1)?'oms_index':$this->tb;
           
            load_model('sys/CarryDataModel')->save_data($tb, $this->move_data);
        }
    }

    function get_move_data() {
        $key = &$this->tb_key;

        $kh_id = CTX()->saas->get_saas_key();
        $task_sn = $this->param['task_sn'];
        $sql = "select  t.*,'{$task_sn}' as carry_task_sn,'{$kh_id}' as carry_kh_id from {$this->tb} t where " . $this->get_condition_where();
        $data = $this->db->get_all($sql);
        if(empty($data)){
             if(isset($this->param['max_id'])&&!empty($this->param['max_id'])){
                 $this->set_max_id($this->param['max_id']);
             }
            return false;
        }
        
        $this->code_data = array();
        foreach ($data as $val) {
            $key_num = $this->get_code_num($val[$key]);
            $this->code_data[$key_num][] = $val[$key];
            $this->move_data[$key_num][] = $val;
            $key_id = $this->tb_config['key_id'];
            $this->set_max_id($val[$key_id]);
        }
        return  true;
    
        
    }

    function get_child_move_data() {
        $key = &$this->tb_key;

        if (isset($this->tb_config['key_type'])) {

            $key = $this->tb_config['key'][1];
        }

        $task_sn = $this->param['task_sn'];
        $kh_id = CTX()->saas->get_saas_key();

        $sql = "select  t.*,'{$task_sn}' as carry_task_sn,'{$kh_id}' as carry_kh_id from {$this->tb} t where " . $this->get_condition_where();

        $data = $this->get_carry_task_data($this->param['parent_task_code'], $this->param['task_type']);
        if (isset($this->tb_config['key_type'])) {

            $this->get_child_key_data($this->tb_config['key'], $data);
        }
        foreach ($data as $key_num => $data_code) {
            
            //临时增加，因为子数据量可能过大导致内存溢出
            if(!empty( $this->move_data)){
                 $this->save_move_data();
                 $this->move_data = array();
            }

            $code_str = "'" . implode("','", $data_code) . "'";

            $new_sql = $sql . " AND {$key} in ({$code_str})";
            $new_data = $this->db->get_all($new_sql);
            if(empty($new_data)){ 
                continue;
            }
            $this->move_data[$key_num] = $new_data;
            $this->child_data_num += count($this->move_data[$key_num]);
           
        }
    }

    function get_index_move_data() {
        $key = &$this->tb_key;
        $index_type = $this->tb_config['index_type'];
        $index_val = $this->tb_config['index_val'];
        $task_sn = $this->param['task_sn'];
        $kh_id = CTX()->saas->get_saas_key();
        $select = "{$key} as record_code ,'oms_sell_record' as record_type ,'{$index_type}' as index_type,{$index_val} as index_val ,{$task_sn} as carry_task_sn,{$kh_id} as carry_kh_id ";
        $sql = "select DISTINCT {$select} from {$this->tb} t where " . $this->get_condition_where();

        
        $data = $this->get_carry_task_data($this->param['parent_task_code'], $this->param['task_type']);


        foreach ($data as $data_code) {
            $code_str = "'" . implode("','", $data_code) . "'";
            $new_sql = $sql . " AND {$key} in ({$code_str})";
            $index_data = $this->db->get_all($new_sql);
            if(empty($index_data)){
                continue;
            }
            $this->child_data_num += count($index_data);
            foreach ($index_data as $val) {
                $code = $val['index_val'];
                $key_num = $this->get_code_num($code, 1000);
                $this->move_data[$key_num][] = $val;
            }
        }
    }

    function get_condition_where() {
        $where = isset($this->tb_config['condition']['where']) ? $this->tb_config['condition']['where'] : ' 1=1  ';
        
        if ($this->is_child == 0) {
            $key_id = $this->tb_config['key_id'];
            $end_date = $this->end_date;
            if ($this->tb_config['condition']['type'] == 'datetime') {
                $end_date.=' 23:59:59';
            }
            $where .= " AND " . $this->tb_config['condition']['time_key'] . " <='{$end_date}' ";
        

            if (isset($this->param['max_id'])) {
                $max_id = $this->param['max_id'];
                $where = " {$key_id}>{$max_id} AND " . $where;
            }
            $limit = isset($this->param['page_size'])?$this->param['page_size']:10000;
            
            $where.=" order by {$key_id} limit {$limit}  "; 
        }

        return $where;
    }

    function get_child_key_data($key_arr, &$data) {
        $table = $this->param['p_table'];
        $conf = $this->carry_conf[$table];
        $p_key = $key_arr[0];
        $code_arr = array();
        foreach ($data as $val_arr) {
            $code_arr = array_merge($val_arr, $code_arr);
        }
   
        $deal_code_list = array();

        $code_str = "'" . implode("','", $code_arr) . "'";
        $sql = "select {$p_key} as code from {$table} where {$conf['key']} in ({$code_str}) ";
        $code_data = $this->db->get_all($sql);
        $new_data = array();
        foreach ($code_data as $val) {
            $arr = array();

            $code_val_str = str_replace("'", '', $val['code']);//处理特殊情况 交易号 带' 符号
            
            $arr[] = trim($code_val_str);
            if(strpos($code_val_str, ',')!==false){
                $arr = explode(',', $code_val_str);
            }
            foreach ($arr as $code) {
                $code = trim($code);
                $key_num = $this->get_code_num($code);
                    $new_data[$key_num][] = $code;
                     if($p_key=='deal_code_list'){
                         $deal_code_list[$code] = array(
                             'deal_code'=>$code,
                              'num'=>$key_num,
                         );
                     }

            }
        }
        
        if(!empty($deal_code_list)){
            $this->insert_multi_exp('api_deal_code', $deal_code_list, true);
        }

        $data = $new_data;
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
            $row['task_type'] = 'move';
            $row['task_code'] = $table;
            $task_param['table'] = $table;
            $row['task_param'] = json_encode($task_param);
            $this->task_data[] = $row;
        }

        //最后处理
        if(isset($this->tb_config['index'] )){
            foreach ($this->tb_config['index'] as $table => $conf) {
                $row = $default_row;
                $row['task_type'] = 'move';
                $row['task_code'] = $table;
                $task_param['is_index'] = 1;
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
                if($num==0){
                    $this->start_task($this->param['parent_task_code'], 'move');
                }
        }else{
            $this->start_child_task($data[0]);
        }
        
        
    }

    function set_max_id($id) {
        if ($id > $this->max_id) {
            $this->max_id = $id;
        }
    }

}
