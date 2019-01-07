<?php

require_model('oms/SellProblemModel');

class BuyerRemarkModel extends SellProblemModel{
	//有买家留言
	function handler($sell_record_data){
	    if($sell_record_data['buyer_remark']!=''){
	    	return $this->format_ret(1);
	    }
	    return $this->format_ret(-1);
	}
}
