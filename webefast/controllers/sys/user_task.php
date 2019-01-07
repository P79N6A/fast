<?php
class user_task {

        function create_task(array & $request, array & $response, array & $app) {
            $type = isset($request['type'])?$request['type']:'';
            unset($request['type']);
            $response = load_model('sys/UserTaskModel')->create_task($request,$type);
        }
        
         function get_status(array & $request, array & $response, array & $app) {
            $response = load_model('sys/UserTaskModel')->get_task_status($request);
        }
}
