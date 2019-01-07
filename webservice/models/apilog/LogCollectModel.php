<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogCollectModel
 *
 * @author wq
 */
require_model('apilog/ApiLogBaseModel');

class LogCollectModel extends ApiLogBaseModel {

    private $log_db;

    function clear_log_data() {
        
    }

    function user_logs_collect() {
        $this->create_base_db();
        $date = date('Y-m-d', strtotime("-7 days"));
        $sql = "select db_name,tb_name,log_date from user_logs.logs_tb_info where is_del=0 AND log_date<'{$date}'";

        $data = self::$base_db->get_all($sql);
        foreach ($data as $val) {
            $this->get_api_num_data($val);
            $this->drop_log_data($val['db_name'], $val['tb_name']);
        }
    }

    function get_api_num_data($log_val) {
        try{
        $db_name = $log_val['db_name'];
        $tb_name = $log_val['tb_name'];
        $this->set_log_db($db_name);
        
        $sql = "show TABLES like '{$db_name}.{$tb_name}' ";
        $is_tb = $this->db->get_all_col($sql);
        if(empty($is_tb)){
            return FALSE;
        }
        
        $sql = "select DISTINCT kh_id,method,type from {$db_name}.{$tb_name}";
        $data_type = $this->log_db->get_all($sql);
        $logs_collect = array();


        foreach ($data_type as $val) {
            $val['logs_date'] = $log_val['log_date'];
            $logs_collect[] = $this->get_collect_data($db_name, $tb_name, $val);
        }
        if (!empty($logs_collect)) {
            $tb = new TbModel('user_logs_collect', '', self::$base_db);
            $update_str = " logs_num = VALUES(logs_num) , fail_num = VALUES(fail_num) ";
            $tb->insert_multi_duplicate('user_logs_collect', $logs_collect, $update_str);
        }
        }  catch (Exception $ex){
            
        }
        return true;
    }

    function drop_log_data($db_name, $tb_name) {
        $this->set_log_db($db_name);
        $this->log_db->query(" drop TABLE IF EXISTS {$db_name}.{$tb_name} ");
        $sql = "update logs_tb_info set is_del=1 WHERE  db_name='{$db_name}' AND db_name='{$tb_name}' ";
        self::$base_db->query($sql);
    }

    private function get_collect_data($db_name, $tb_name, $api_val) {

        $data = array();
        if (!empty($api_val['logs_date'])) {
            $data['logs_date'] = $api_val['logs_date'];
        } else {

            //todo test

            $tb_arr = explode("_", $tb_name);
            $date = end($tb_arr);
            $data['logs_date'] = date('Y-m-d', strtotime($date));
        }


        $data['kh_id'] = $api_val['kh_id'];
        $data['api_type'] = $api_val['type'];
        $data['api_method'] = $api_val['method'];
        $data['logs_date'] = $api_val['method'];


        $sql = "select count(1) from {$db_name}.{$tb_name} where 1 ";
        $sql.="  AND kh_id= '{$api_val['kh_id']}' AND method= '{$api_val['method']}' AND type= '{$api_val['type']} '  ";
        $data['logs_num'] = $this->log_db->get_value($sql);
        $sql.=" AND  is_err=2";
        $data['fail_num'] = $this->log_db->get_value($sql);

        $data['create_time'] = time();
        return $data;
    }

    private function set_log_db($db_name) {
        $this->log_db = $this->get_db($db_name);
    }

}
