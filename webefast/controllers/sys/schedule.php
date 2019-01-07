<?php
require_lib('util/web_util', true);
class schedule {
    function do_list(array &$request, array &$response, array &$app) {
        $response['service_sap'] = load_model('common/ServiceModel')->check_is_auth_by_value('14000000');
    }
    
	function update_active(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/SysScheduleModel')->update_status( $request['id'],$request['type']);
        exit_json_response($ret);
    }
    
    //检查执行状态
    function check_execute_status(array &$request, array &$response, array &$app){
    	$code_arr = explode(",", $request['code']);
    	$ret = array();
    	foreach ($code_arr as $code){
    		$execute_status = load_model('common/TaskModel')->get_status_by_code($code);
    		$ret[] = array($code => $execute_status['is_over']);
    	}
    	exit_json_response(1,$ret,'');
    }
    
    //立即执行
    function execute_right_off(array &$request, array &$response, array &$app) {
    	$customer_code = CTX()->saas->get_saas_key();
    	$ret = load_model('common/TaskModel')->exec_sys_schedule($request['code'],$customer_code);
//     	$ret = array('status' => 1);
        load_model('sys/SysScheduleModel')->execute_right_off_log($request['code']);        
    	exit_json_response($ret);
    }
    //立即执行efast_api
    function execute_right_off_api(array &$request, array &$response, array &$app) {
        set_time_limit(0);
        $ret = load_model('sys/ScheduleModel')->execute_schedule_api($request['code']);
    	exit_json_response($ret);
    }
    
    function table(array &$request, array &$response, array &$app){
        if (isset($request['type']) && $request['type'] != '') {
	    	 $response['type'] = $request['type'];
	    }
    }
    
    function open_close_service(array &$request, array &$response, array &$app){
    	global $context;
    	static $s_dict = NULL;
	    if ($s_dict == NULL) {
			$filename = ROOT_PATH.($context->app_name).'/conf/export/schedule_service_list.conf.php';
			if (!file_exists($filename)) {
				$GLOBALS['context']->log_error('file not found! '.$filename);
				return array();
			}
			$schedule_list = include $filename;
		}
    	$service_type = $schedule_list[$request['type']];
    	$response = load_model('sys/SysScheduleModel')->open_close_service($service_type);
    }
        function get_task_url(array &$request, array &$response, array &$app){
                
                        $sql = "select * from sysdb.sys_task_main where id='{$request['id']}'";
                        $row = CTX()->db->get_row($sql);
                        $task_param = json_decode($row['request'],true);
                        //var_dump($task_param);die;
                        $url = "http://localhost/efast/webefast/web/?";
                        foreach($task_param as $k=>$val){
                            $url .=$k."=".$val."&";
                        }
                        echo $url;die;


    }
}
