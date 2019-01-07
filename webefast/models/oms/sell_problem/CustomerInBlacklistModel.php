<?php

require_model('oms/SellProblemModel');

class CustomerInBlacklistModel extends SellProblemModel{
	//会员是黑名单
	function handler($sell_record_data){

	    $sql = "select count(1) from crm_customer where customer_code = :customer_code  and type>1";
	    $count = CTX()->db->get_value($sql,array(':customer_code'=>$sell_record_data['customer_code']));

	    if ($count>0){
	    	return $this->format_ret(1);
	    }
	    return $this->format_ret(-1);
	}
}
