<?php

/**
 * Description of SysCarryTaskModel
 *
 * @author wq
 */
require_model('tb/TbModel');

class SysCarryTaskModel extends TbModel {

    //put your code here
    function __construct() {
        parent::__construct('sys_carry_task');
    }



    function update_task($task_type, $task_code,$parent_task_code,$data) {
        $where = " task_type='{$task_type}' AND task_code='{$task_code}' AND parent_task_code='{$parent_task_code}' ";
        return $this->update($data, $where);
    }

    function create_task_more($data) {
       // var_dump($data);die;

        $update_str = 'status = VALUES(status),sys_task_id = VALUES(sys_task_id)';
        $this->insert_multi_duplicate($this->table, $data, $update_str);
    }
    function clear_task(){
        $sql = " TRUNCATE sys_carry_task ";
        $this->db->query($sql);
        
    }

}
