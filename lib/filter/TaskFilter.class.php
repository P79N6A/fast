<?php

require_once ROOT_PATH . 'boot/req_inc.php';

class TaskFilter implements IReponseFilter {

    function handle_after(array & $request, array & $response, array & $app) {
        if ($app['mode'] == 'cli') {//命令行
            if (isset($request['__t_sn'])) {
                if (isset($request['__p_id'])) {//多进程任务直接结束
                    load_model('common/TaskModel')->over_process($request['__p_id']);
                }else if(isset($request['__t_id'])) {//普通任务
                    if($response['status']>0){
                        if($response['status']==100){
                            load_model('common/TaskModel')->task_exec_loop($request['__t_id']);
                        }else{
                          //  load_model('common/TaskModel')->over_task($request['__t_id']);
                          $this->over_task($request,$response);
                        }
                    }else{
                        load_model('common/TaskModel')->over_task($request['__t_id'],3);
                    }
                }
            }
        }
        return true;
    }
    
    private function over_task(&$request,&$response){
             $tmod =   load_model('common/TaskModel');
             $tmod->over_task($request['__t_id']);
             return true;
             
             $task_tree = require_conf('task_tree');
             $task_data =  $tmod->get_task($request['__t_id']);
             if(isset($task_tree[$task_data['code']])){
                   $task_conf = &$task_tree[$task_data['code']];
                   foreach($task_conf as $code=>$val){
                       if(($val['check']==1&&!empty($response['data']))||$val['check']==0){
                            $customer_code = CTX()->saas->get_saas_key();  
                            $ret = $tmod->exec_sys_schedule($code, $customer_code,1);   
                            
                            //修改下次执行时间
                            if($ret['status']>0){
                                $tmod->update_schedule_plan_time($code);
                            }
                       }
                   }
             }
             
   
             
    }

}

