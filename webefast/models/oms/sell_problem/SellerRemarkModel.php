<?php

require_model('oms/SellProblemModel');

class SellerRemarkModel extends SellProblemModel{
	//有卖家留言
	function handler($sell_record_data){
	    if($sell_record_data['seller_remark']!=''){
	    	return $this->format_ret(1);
	    }
	    return $this->format_ret(-1);
	}
}
