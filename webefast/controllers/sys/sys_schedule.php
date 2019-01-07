<?php
require_lib ('util/web_util', true);
class sys_schedule {
    function do_list(array &$request, array &$response, array &$app) {
        $response['sale_channel'] = $this->get_channel();
        $response['sys_schedule'] = require_conf('sys/sys_schedule');
    }


    function set_schedule_status(array &$request, array &$response, array &$app) {
          $ret  = load_model('sys/SysScheduleModel')->update_status($request['id'],$request['status']);
          exit_json_response($ret);
    }
    function get_schedule_shop(array &$request, array &$response, array &$app) {
           $ret = load_model('sys/SysScheduleModel')->get_schedule_shop($request['_id']);
            
            $response['shop_list']  = $ret['data'];
            $response['id'] = $request['_id'];
    }
   function set_schedule_shop(array &$request, array &$response, array &$app) {
            $ret = load_model('sys/SysScheduleModel')->set_schedule_shop($request['id'],$request['shop_id'],$request['status']);
            exit_json_response($ret);
    }

    function get_channel(){
    	$arr_channel = array(array('0'=>'','1'=>'请选择'),array('0'=>'9','1'=>'淘宝'),array('0'=>'13','1'=>'京东'),                
        		           array('0'=>'16','1'=>'一号店'),array('0'=>'10','1'=>'拍拍'),
        		           array('0'=>'14','1'=>'亚马逊'),);
    	return $arr_channel;
    }
    
     function get_task_log(array &$request, array &$response, array &$app) {
         
          $response = load_model('common/TaskModel')-> read_task_log($request['task_id'], $request['log_file_offset']);
          
    }
   	function do_task(array & $request, array & $response, array & $app) {
            $request['sleep'] = isset($request['sleep'])?$request['sleep']:10;
            sleep($request['sleep']);
            echo "is test run";
            $response['status'] = 1;
	}
        function test_task(array & $request, array & $response, array & $app){
            
        }
        
        function c_task(array & $request, array & $response, array & $app) {
            require_model('common/TaskModel'); 
            $task = new TaskModel();
            $data['code'] = 'test_task';
            $tast_request = array(
                    'app_fmt'=>'json',
                    'app_act'=>'sys/sys_schedule/do_task',
                    'sleep'=>10,
                );
               $data['request'] = $tast_request;
               $ret = $task->save_task($data);
               $response = $ret;
        }
        function get_test_task(array & $request, array & $response, array & $app) {
              require_model('common/TaskModel'); 
              $task = new TaskModel();
             $response = $task->get_task_status($request['task_id']);
        }

}
