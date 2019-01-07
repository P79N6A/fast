<?php

class report_alipay {
    function do_list(array & $request, array & $response, array & $app){
       
    }
    function search(array & $request, array & $response, array & $app){
    	$ret = load_model('acc/ReportAlipayModel')->list_data($request);
    	$response = $ret;
    }


}