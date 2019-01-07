<?php
require_model('tb/TbModel');
require_lib('util/oms_util', true);

/**
 * 仓库适配策略
 */
class PolicyStoreModel extends TbModel
{
	function get_table() {
		return 'op_policy_store';
	}
    
    /**
     * 
     * @param $store_code
     * @return array
     */
    public function get_by_code($store_code){
        return $this->get_row(array('store_code'=>$store_code));
    }
    
    function get_by_page($filter) {
        $sql_main = "FROM base_store rl LEFT JOIN op_policy_store ps ON rl.store_code=ps.store_code   WHERE 1 ";
        $sql_values = array();
   
        
 

        $select = 'rl.store_code,rl.store_name,ps.sort,ps.area_desc';
        $sql_main .= " order by rl.store_id ";
  
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {

            if(empty($value['area_desc'])){
                $value['area_names'] = '全国';
            }else{
                 $area_name_arr = $this->get_area_name_by_area_id($value['area_desc']);
                 $value['area_names'] = implode(",", $area_name_arr);
            }
     
            $value['sort'] = empty($value['sort'])?0:$value['sort'];	
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    function get_area_name_by_area_id($area_id){

        $sql = "select name  from base_area where id in({$area_id}) ";
        $data = $this->db->get_all($sql);
        $area_name_arr = array();
        
        foreach($data as $val){
           $area_name_arr[] =  $val['name'];
        }
        return $area_name_arr;
    }
    function get_area_name($area_name_arr){
            $area_name = '全国';
            if(!empty($area_name_arr)){
                if(count($area_name_arr)>20){
                   $area_name_arr =  array_slice($area_name_arr,0,20);
                    $area_name = implode(',', $area_name_arr)."...";
                }else{
                     $area_name = implode(',', $area_name_arr);
                }
                
            }
           return $area_name;
    }


    
    function set_sort($store_code,$sort){
        $data[] =  array('store_code'=>$store_code,'sort'=>$sort);
        $update_str = " sort = VALUES(sort) ";
        return $this->insert_multi_duplicate($this->table, $data, $update_str);
        
    }
    
    function update_area_desc($store_code,$area_arr){
        $area_desc = implode(",", $area_arr);
        $data[] =  array('store_code'=>$store_code,'area_desc'=>$area_desc);
        $update_str = " area_desc = VALUES(area_desc) ";
        return $this->insert_multi_duplicate($this->table, $data, $update_str);  
        
    }
    
    
    
    
}