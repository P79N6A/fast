<?php
/*
 * 零售结算交易核销查询
 */
/**
 * Description of retail_settlement_detail
 *
 * @author user
 */
class sell_settlement {
    function do_list(array & $request, array & $response, array & $app){
    	$response = load_model('acc/OmsSellSettlementModel')->sellsettlement_total_amount($request);
    }
    function record_list(array & $request, array & $response, array & $app){
    }
    
    function get_record_detail(array & $request, array & $response, array & $app){
    	$data = load_model("acc/OmsSellSettlementModel")->get_record_detail($request['sell_record_code'],$request['deal_code'],$request['order_attr']);
    	$response = array('rows'=>$data);
    }
    function do_check_account(array & $request, array & $response, array & $app){
    	$response = load_model('acc/OmsSellSettlementModel')->do_update_check_status($request['deal_code']);
    }
    function do_cancel_account(array & $request, array & $response, array & $app){
        $response = load_model('acc/OmsSellSettlementModel')->do_update_cancel_status($request['deal_code']);
    }
    //合计
    function total_amount_search(array & $request, array & $response, array & $app){
    	$response = load_model('acc/OmsSellSettlementModel')->sellsettlement_total_amount($request);
    }
    
}