<?php

require_model('oms/SellProblemModel');

class ExpressCheckModel extends SellProblemModel{
	//韵达快递区域是否可达
	function handler($sell_record_data){

           $func = $this->get_check_fun($sell_record_data['express_code']);

            if($func!=''){
                $status = $this->$func($sell_record_data);
  
                return $this->format_ret($status);
            }
	    return $this->format_ret(-1);
	}
        

        private function check_address_yunda(&$sell_record_data){
            $param = array();
            $order = array('id'=>$sell_record_data['sell_record_code'],'address'=>$sell_record_data['receiver_address']);
            $param['orders']['order'] = $order;
            $ret = load_model('api/YDApiModel')->request_api($param);
             if($ret===false){
                 return -1;
             }else{
                return   (isset($ret['reach'])&&$ret['reach']==0)?1:-1;
             }
            
        }
        
        private function get_check_fun($express_code){
            $sql = "select company_code from base_express where express_code=:express_code";
            $company_code = $this->db->get_value($sql,array(':express_code'=>$express_code));
            $func = '';
            $express_comany = array('YUNDA');
            if(in_array($company_code, $express_comany)){
                  $func = 'check_address_'.strtolower($company_code);
            }
             return $func;
        }
        
        
}
