<?php

require_model('oms/SellProblemModel');

class InvoiceTradeModel extends SellProblemModel{
	//有发票
	function handler($sell_record_data){
        if(isset($sell_record_data['invoice_status'])&&$sell_record_data['invoice_status']>0){
			return $this->format_ret(1);
        }
	    return $this->format_ret(-1);
	}
}
