<?php
require_lib ( 'util/web_util', true );
require_lib ( 'app_util', true );
class Operate {
       function __construct() {
            CTX()->response['app_main_index'] = 1;
        }
  
        function do_index(array & $request, array & $response, array & $app) {
            $app['page'] = 'NULL';
            $response['title'] = '运营分析';
        }
        
        function goods_sell_ranking(array & $request, array & $response, array & $app) {
             $app['page'] = 'NULL';
            $response['title'] = '商品销售排行';   
        }
        
        function get_goods_sell_ranking(array & $request, array & $response, array & $app){
            $app['fmt'] = 'json';
            $response = load_model('oms/OmsReportDaySkuModel')->get_report_data($request['day']);
        }
        
        function sell_analysis(array & $request, array & $response, array & $app){
                 $app['page'] = 'NULL';
            $response['title'] = '销售分析';   
            
        }
        function get_sell_analysis(array & $request, array & $response, array & $app){
               $app['fmt'] = 'json';
               $response = load_model('crm/OperateModel')->get_sell_analysis($request['day']);
        }
      
        
}