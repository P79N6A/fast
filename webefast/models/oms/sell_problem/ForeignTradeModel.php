<?php

require_model('oms/SellProblemModel');

class ForeignTradeModel extends SellProblemModel{
	//如果省一级 没数据，识别为国外地址
	function handler($sell_record_data){

	    if (empty($sell_record_data['receiver_province'])||$sell_record_data['receiver_province']=='250000'){
			return $this->format_ret(1);
	    }
	    return $this->format_ret(-1);
	}
	
}
