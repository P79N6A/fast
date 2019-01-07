<?php
/*
 * 订单监控
 */
/**
 * Description of retail_settlement_detail
 *
 * @author user
 */
class sell_record_monitor {
    function do_list(array & $request, array & $response, array & $app){
    	$response = load_model('api/OrderMonitorModel')->total_amount_search($request);
    }
    //合计
    function total_amount_search(array & $request, array & $response, array & $app){
    	$response = load_model('api/OrderMonitorModel')->total_amount_search($request);
    }
   
}