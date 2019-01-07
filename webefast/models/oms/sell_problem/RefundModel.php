<?php

require_model('oms/SellProblemModel');

class RefundModel extends SellProblemModel{
	//部分退款的
	function handler($sell_record_data){
//	    $sql = "select count(*) from api_refund where tid = :tid and is_change <= 0 and status = 1";
//	    $count = ctx()->db->getOne($sql,array(':tid'=>$sell_record_data['deal_code']));
//	    if ($count>0){
//	    	return $this->format_ret(1);
//	    }	    
//	    return $this->format_ret(-1);
//            $sql = "select id from api_refund where tid = :tid and is_change <= 0 and status = 1";
//            $id = ctx()->db->getOne($sql,array(':tid'=>$sell_record_data['deal_code']));
//            if (empty($id)){
//	    	return $this->format_ret(-1);
//	    }	    
//            $api_data = load_model('oms/TranslateRefundModel')->get_refund_api($id);
            $data = load_model('oms/TranslateRefundModel')->is_full_return($sell_record_data['deal_code'],$sell_record_data);
            if($data == 2) {
                return $this->format_ret(1);
            }
	    return $this->format_ret(-1);
	}
}
