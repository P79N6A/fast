<?php

class alipay_account_item {
    function do_list(array & $request, array & $response, array & $app){

    }
    function search(array & $request, array & $response, array & $app){
    	$ret = load_model('acc/AlipayAccountItemModel')->list_data($request);
    	echo $ret;
    	die;
    }


}