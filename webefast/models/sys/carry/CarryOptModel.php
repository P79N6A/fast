<?php

require_model('tb/TbModel');
set_time_limit(0);
ini_set('memory_limit', '1024M');
class CarryOptModel extends TbModel {

    function create_task($param = array()) {
        require_model('common/TaskModel');
        $task = new TaskModel();
        $task_data = array();

        $task_data['code'] = $param['type'];
        $request['app_act'] = 'sys/sys_carry/action_task';
        $request['app_fmt'] = 'json';
        $task_data['request'] = $param;
        $ret = $task->save_task($task_data);
        if ($ret['status'] < 0) {
            if ($ret['status'] == -5) {
                $ret['message'] = "任务创建异常";
            }
        }
        return $ret;
    }

    function exec(&$param) {

        $mod_arr = $this->get_mod($param['task_type'], $param['task_code']);
        $ret = $mod_arr['class']->$mod_arr['method']($param);

        return $ret;
    }

    function get_mod($task_type, $task_code) {
        $mod_arr = array();
        if ($task_type == 'main') {
            $mod = 'Carry' . ucfirst($task_code) . 'Model';
            $mod = load_model('sys/carry/' . $mod);
            $mod_arr['class'] = $mod;
            $mod_arr['method'] = 'init';
        } else {
            $mod = 'Carry' . ucfirst($task_type) . 'Model';
            $mod = load_model('sys/carry/' . $mod);
            $mod_arr['class'] = $mod;
            $mod_arr['method'] = 'exec'; 
        }
        return $mod_arr;
    }
    
    function check_task(){
        $sql = "select * from sys_carry_task where status<>2 AND sys_task_id>0";
        $data = $this->db->get_all($sql);
        
        foreach($data as $val){
            
        }
        
        
    }

}
