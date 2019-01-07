<?php
/**
 * 用户相关业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');

class SysScheduleModel extends TbModel {
    

    function create_task(){
        require_model('common/TaskModel'); 
        $task = new TaskModel();
        $conf = require_conf('task');
        $db_name = CTX()->get_app_conf('db_name');
        $task->create_customer_task('', $db_name, $conf['ip']) ;
    }
    
    
}