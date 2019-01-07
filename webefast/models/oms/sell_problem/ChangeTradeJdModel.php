<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ChangeTradeJdModel
 *
 * @author wq
 */
require_model('oms/SellProblemModel');
class ChangeTradeJdModel extends SellProblemModel{
    	//如果省一级 没数据，识别为国外地址
	function handler($sell_record_data){
	    if (isset($sell_record_data['is_change_record'])&&$sell_record_data['is_change_record']==1&&($sell_record_data['sale_channel_code']=='jingdong'||$sell_record_data['sale_channel_code']=='biyao')){
			return $this->format_ret(1);
	    }
	    return $this->format_ret(-1);
	}
}



