<?php
require_lib ( 'util/web_util', true );
require_lib ( 'app_util', true );
class Monitor {
        function __construct() {
            CTX()->response['app_main_index'] = 2;
        }
            
        function api_record(array & $request, array & $response, array & $app) {
            $app['page'] = 'NULL';
            $response['title'] = '漏单监控';
            
            $response['date_arr'] = load_model('oms/OmsReportDayModel')->get_date(3);

        }
        function do_index(array & $request, array & $response, array & $app) {
            $app['page'] = 'NULL';
            $response['title'] = '系统监控';
               //var_dump($response);die;
            
        }
        function get_api_record(array & $request, array & $response, array & $app) {
             $app['fmt'] =  "json";
             $response =  load_model("api/OrderMonitorModel")->get_data_by_date($request['date']);
             $response['update_time'] = isset($response['data'][0])?$response['data'][0]['insert_time']:'';
        }
    

}