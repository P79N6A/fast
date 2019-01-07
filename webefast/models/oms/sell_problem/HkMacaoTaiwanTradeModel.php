<?php

require_model('oms/SellProblemModel');

class HkMacaoTaiwanTradeModel extends SellProblemModel{
	//香港、澳门、台湾订单
	function handler($sell_record_data){
	    $_addr = $sell_record_data['receiver_province'];
            //香港 810000 澳门 820000 台湾 710000
	    if ( strpos($_addr,'810000') !== false || strpos($_addr,'820000') !== false || strpos($_addr,'710000') !== false){
	    	return $this->format_ret(1);
	    }
	    return $this->format_ret(-1);
	}
}
