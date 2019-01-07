<?php

/**
 * 数据对比
 *
 * @author wq
 */
require_model('sys/carry/CarryBaseModel');
class CarryCompareModel extends CarryBaseModel {

    function exec(&$param) {
        $is_init = $this->init_action($param);
        if ($is_init === false) {
            return $this->format_ret(1);
        }
        $this->save_move_data_num();
        $status = $this->compare_data();
        //$status = true;
        if ($status === TRUE) {
            $this->update_status(4);
            $status = $this->check_task('compare');
      
            if ($status === true) {
                //同时开启索引建立
                $this->start_index_start();
            }
        }
        return $this->format_ret(1);
    }

    function save_move_data_num() {
        $task_sn = $this->param['task_sn'];
        foreach ($this->carry_conf as $table => $conf) {
            if($conf['type']=='del'){
                continue;
            }
            $num = load_model('sys/CarryDataModel')->get_move_data_num($table, $task_sn);
         //var_dump($table, $num);die;
            $this->save_move_num($table, $num);
        //    var_dump($table, $num);die;
            foreach ($conf['move'] as $tb => $val) {
                $num = load_model('sys/CarryDataModel')->get_move_data_num($tb, $task_sn);
                $this->save_move_num($tb, $num);
            }
            if(isset($conf['index'])){
                foreach ($conf['index'] as $tb => $val) {
                    //暂时只有一个索引数据
                    $num = load_model('sys/CarryDataModel')->get_move_data_num('oms_index', $task_sn,1000);
                    $this->save_move_num($tb, $num);
                }
            }
        }
        
        $this->other_move_data_num();
        
        
        
        
        
    }
    function other_move_data_num(){
          $task_sn = $this->param['task_sn'];
            $sql = "select count(1) from  oms_sell_settlement t INNER JOIN api_deal_code d
                ON t.deal_code=d.deal_code ";
         $num =   $this->db->get_value($sql);
         $data['sys_num'] = $num;
         $data['del_num'] = $num;
         $where = " task_sn='{$task_sn}' AND task_tb='oms_sell_settlement' ";
         $this->update_exp('sys_carry_data', $data, $where);
        
        
    }

    function compare_data() {
        $sql = "select * from sys_carry_data where task_sn=:task_sn AND sys_num>0";
        $sql_value = array(
            ':task_sn' => $this->param['task_sn'],
        );
        $data = $this->db->get_all($sql, $sql_value);
        $msg_arr = array();
        foreach ($data as $val) {
            if ($val['sys_num'] != $val['move_num']) {
                $msg_arr[] = $val['task_tb'] . "系统数据：" . $val['sys_num'] . ",移出数据" . $val['move_num'];
            }
        }

        if (!empty($msg_arr)) {
            $this->update_msg(implode(',', $msg_arr));
            return FALSE;
        }
        return TRUE;
    }

}
