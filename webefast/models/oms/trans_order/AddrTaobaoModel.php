<?php
require_model('tb/TbModel');
class AddrTaobaoModel extends TbModel{
	
	//地址匹配
    function match_addr($api_data){
        //地址匹配
        $addr_ret = load_model('base/TaobaoAreaModel')->get_by_field_all($api_data['receiver_country'],$api_data['receiver_province'],$api_data['receiver_city'],$api_data['receiver_district'],$api_data['receiver_addr']);
	
        if (empty($addr_ret['country_id'])){
            return $this->format_ret(-30,'','地址匹配找不到国家');		            
        }
        
        if (empty($addr_ret['province'])){
            return $this->format_ret(-30,'','地址匹配找不到省信息');		            
        }
        //特殊走系统地址
//        if(empty($addr_ret['city'])){
//            $addr_ret['city'] = $this->get_sys_city($api_data['receiver_city'],$addr_ret['province']);
//        }
        
        if (empty($addr_ret['city'])){
            return $this->format_ret(-30,'','地址匹配找不到市信息');		            
        }       
        
        
        
		$result['receiver_country'] = $addr_ret['country_id'];
		$result['receiver_province'] = $addr_ret['province'];
		$result['receiver_city'] = $addr_ret['city'];
		$result['receiver_district'] = $addr_ret['district'];
		$result['receiver_street'] = $addr_ret['street'];
		$result['receiver_address'] = $api_data['receiver_address'];	            
        if (!empty($addr_ret['street'])){
            $result['receiver_addr'] = str_replace($addr_ret['street_name'],'',$api_data['receiver_addr']);
        }else{
            $result['receiver_addr'] = $api_data['receiver_addr'];	            
        }
		return $this->format_ret(1,$result);	    
    }
    
    function get_sys_city($api_city_name,$province_name){
        $data = $this->db->get_all("select name,parent_id from base_area where name like '{$api_city_name}%' and type=3 ");
        $city_name = '';
        foreach ($data as $row){

                $province_name_by_city_name = $this->db->get_value("select name from base_area where  parent_id = '{$row['parent_id']}'  ");
                if($province_name_by_city_name==$province_name){
                   $city_name = $row['name'];
                   break;
                }
            }

        return $city_name;
    }
    
}
