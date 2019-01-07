<?php

require_lib('util/web_util', true);

class carry {

    function do_list(array &$request, array &$response, array &$app) {
        
    }

    function set_carry(array &$request, array &$response, array &$app) {

        $response['start_date'] = load_model('sys/carry/SysCarryModel')->get_start_date();
        $response['data'] = load_model('sys/carry/SysCarryModel')->check_carry();
    }

    function do_set_carry(array &$request, array &$response, array &$app) {


        $response = load_model('sys/carry/SysCarryModel')->create_carry($request);
    }
    function action_task(array &$request, array &$response, array &$app) {
                $response = load_model('sys/carry/CarryOptModel')->exec($request);
    }
    
    function dd(array &$request, array &$response, array &$app) {
                 $sql = "select * from sys_carry_task where task_type=:task_type";
            $sql_value = array(
                ':task_type' => 'del',
            );
            $data = CTX()->db->get_all($sql, $sql_value);
            foreach ($data as $val) {

                load_model('sys/carry/CarryBaseModel')->start_task($val['task_code'], $val['task_type']);
            }

        var_dump(111);die;
    }
    
    
    

}
