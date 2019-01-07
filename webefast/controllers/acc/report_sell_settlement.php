<?php

class report_sell_settlement {
    function do_list(array & $request, array & $response, array & $app){
       
    }
    function search(array & $request, array & $response, array & $app){
    	$ret = load_model('acc/ReportSellSettlementModel')->list_data($request);
    	$response = $ret;
    }


}