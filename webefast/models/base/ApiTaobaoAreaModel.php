<?php
/**
* 淘宝系统地址区域相关业务
*
* @author dfr
*/
require_model('tb/TbModel');
require_lang('sys');

class ApiTaobaoAreaModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'api_taobao_area';    
    }
    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name,$value, $select = "*") {
    	 
    	$sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
    	$data = $this -> db -> get_row($sql, array(":{$field_name}" => $value));
    	 
    	if ($data) {
    		return $this -> format_ret('1', $data);
    	} else {
    		return $this -> format_ret('-1', '', 'get_data_fail');
    	}
    }
    /**
     * 通过国家，省，市，区/县，街道得到相应id
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field_all($country,$province,$city,$district,$street) {
    	$sql = "select * from {$this->table} where name = :country";
    	$country_arr = $this -> db -> get_row($sql, array(":country" => $country));
    	$country_id = isset($country_arr['id'])?$country_arr['id']:'';
    	//省
    	$sql = "select * from {$this->table} where name = :province and parent_id = :country_id";
    	$province_arr = $this -> db -> get_row($sql, array(":province" => $province,":country_id" => $country_id));
    	$province_id = isset($province_arr['id'])?$province_arr['id']:'';
    	//市
    	$sql = "select * from {$this->table} where name = :city and parent_id = :province_id";
    	$city_arr = $this -> db -> get_row($sql, array(":city" => $city,":province_id" => $province_id));
    	$city_id = isset($city_arr['id'])?$city_arr['id']:'';
    	//区县
    	$sql = "select * from {$this->table} where name = :district and parent_id = :city_id";
    	$district_arr = $this -> db -> get_row($sql, array(":district" => $district,":city_id" => $city_id));
    	$district_id = isset($district_arr['id'])?$district_arr['id']:'';
    	
    	//街道
    	$sql = "select * from {$this->table} where name = :street and parent_id = :district_id";
    	$street_arr = $this -> db -> get_row($sql, array(":street" => $street,":district_id" => $district_id));
    	$street_id = isset($street_arr['id'])?$street_arr['id']:'';
    	//print_r($street_id);
    	
    	$arr = array('country_id'=>$country_id,'province'=>$province_id,'city'=>$city_id,'district'=>$district_id,'street'=>$street_id); 
    	return $arr;
    }
}
