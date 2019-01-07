<?php
require_model('tb/TbModel');

class GiftStrategyLogModel extends TbModel {
    function get_table() {
        return 'op_gift_strategy_log';
    }
    
    function get_by_page($filter) {
        
        $sql_main = "FROM {$this->table} rl    	WHERE 1";
        $sql_values = array();
        //策略名称 
        if (isset($filter['strategy_code']) && $filter['strategy_code'] != '') {
            $sql_main .= " AND rl.strategy_code = :strategy_code ";
            $sql_values[':strategy_code'] = $filter['strategy_code'] ;
        }
            $select = 'rl.*';
        $sql_main .= " order by log_id desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        /*foreach ($data['data'] as $key => $value) {
        	$data['data'][$key]['start_time'] = date('Y-m-d H:i',$value['start_time']);
        	$data['data'][$key]['end_time'] = date('Y-m-d H:i',$value['end_time']);
        }*/


        // print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
        
    }
    
    function insert($data) {
        $data['add_time'] = date('Y-m-d H:i:s');
        $data['user_code'] =  CTX()->get_session('user_code');
        $data['user_id'] =  CTX()->get_session('user_id');
      return  parent::insert($data);
    }

    
}