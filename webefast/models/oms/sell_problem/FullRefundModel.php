<?php

require_model('oms/SellProblemModel');

class FullRefundModel extends SellProblemModel{
	//整单退款的
	function handler($sell_record_data){
            $sql = "select id from api_refund where tid = :tid and is_change <= 0 and status = 1";
	    $id = ctx()->db->getOne($sql,array(':tid'=>$sell_record_data['deal_code']));
	    if (empty($id)){
	    	return $this->format_ret(-1);
	    }	    
//            $api_data = load_model('oms/TranslateRefundModel')->get_refund_api($id);
            $data = load_model('oms/TranslateRefundModel')->is_full_return($sell_record_data['deal_code'],$sell_record_data);
            if($data == 1) {
                return $this->format_ret(1);
            }
            
	    return $this->format_ret(-1);
	}
//        function get_deal_code_by_id($sell_record_data){
//            var_dump($sell_record_data['deal_code']);die;
//            $sql = "select id from api_refund where tid = :tid and is_change <= 0 and status = 1";
//            var_dump($sql,$sell_record_data['deal_code']);die;
//	    return ctx()->db->getOne($sql,array(':tid'=>$sell_record_data['deal_code']));
//        }
}
