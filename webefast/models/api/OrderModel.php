<?php
require_model('tb/TbModel');
class OrderModel extends TbModel{
    protected $table = "api_order";
    protected $detail_table = "api_order_detail";
    
    function get_list_by_ids($api_order_ids){
        if(is_array($api_order_ids)){
            $api_order_ids = implode(",", $api_order_ids);
        }
        $sql = "select * from {$this->table} where id in (:api_order_ids)";
        $data = $this->db->get_all($sql,array(':api_order_ids'=>$api_order_ids));
        if($data){
            return $this->format_ret(1,$data);
        }else{
            return $this->format_ret(-1);
        }
    }
    
    function get_detail_list($params,$cols=null, $p2=null){
        $data = $this -> db -> create_mapper($this -> detail_table) ->select($cols)-> where($params) -> find_all_by();
        $ret_status = $data ? 1 : 'op_no_data';
        return $this -> format_ret($ret_status, $data);
    }
    
    function get_row_by_filter($filter){
        $sql = "select * from {$this->table} where shop_code=:shop_code and is_change=:is_change and status=:status and id not in (".$filter['filter'].")";
        $sql_values = array(
            ":shop_code" =>$filter['shop_code'],
            ":is_change" =>$filter['is_change'],
            ":status" =>$filter['status'],
        );
        $data = $this->db->get_row($sql, $sql_values);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this -> format_ret($ret_status, $data);
    }
}
