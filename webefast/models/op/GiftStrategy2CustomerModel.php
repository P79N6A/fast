<?php
require_model('tb/TbModel');

class GiftStrategy2CustomerModel extends TbModel{
    
    function  get_table(){
        return 'op_gift_strategy_customer';
    }

    function get_by_page($filter) {
        
//        strategy_code
             $sql_main = "FROM {$this->table} rl
    	WHERE 1";
        $sql_values = array();
        //策略名称 
        if (isset($filter['strategy_code']) && $filter['strategy_code'] != '') {
            $sql_main .= " AND rl.strategy_code = :strategy_code ";
            $sql_values[':strategy_code'] = $filter['strategy_code'] ;
        } 
        //策略名称
        if (isset($filter['op_gift_strategy_detail_id']) && $filter['op_gift_strategy_detail_id'] != '') {
        	$sql_main .= " AND rl.op_gift_strategy_detail_id = :op_gift_strategy_detail_id ";
        	$sql_values[':op_gift_strategy_detail_id'] = $filter['op_gift_strategy_detail_id'] ;
        }  
        $data = $this -> get_page_from_sql($filter, $sql_main, $sql_values, "*");

        $ret_status = "1";
        $ret_data = $data;

        return $this -> format_ret($ret_status, $ret_data);    
       
    }
            
    

    function insert($data){
    	return parent::insert($data);
    }
    
    function clear_data($op_gift_strategy_detail_id){
        return parent::delete(array('op_gift_strategy_detail_id'=>$op_gift_strategy_detail_id));
    }
    
    function import_data($file,$strategy_code,$op_gift_strategy_detail_id=""){
        $data = array();
        $this->read_data($file,$data,$strategy_code,$op_gift_strategy_detail_id);
        $update_str = " tel = VALUES(tel) ";
        $this->insert_multi_duplicate('op_gift_strategy_customer', $data, $update_str);
        return $this->format_ret(1,count($data));
    }
    private function read_data($file,&$data,$strategy_code,$op_gift_strategy_detail_id=""){

         $file = fopen($file, "r");
         $row_type = array('0'=>'buyer_name','1'=>'tel');
         $i =0 ;
          while (!feof($file)) {
              $row = fgetcsv($file);
            if ($i >= 1) {
                $row = $this->tran_csv($row,$row_type);
                if(!empty($row['buyer_name'])){
                 $row['strategy_code'] = $strategy_code;
                 if (!empty($op_gift_strategy_detail_id)){
                 	$row['op_gift_strategy_detail_id'] = $op_gift_strategy_detail_id;
                 }
                 
                 $data[] = $row;
                }
            }
            $i++;
        }
        fclose($file);  

    }
      private function tran_csv(&$row,$row_type){
        $new_row = array();
       if(!empty($row)){
        foreach($row as $key=>$val){
          //  $val = iconv('gbk','utf-8',$val);
            $val = str_replace('"', '', $val);
            if(isset($row_type[$key])){
                $new_key = $row_type[$key];
                $new_row[$new_key] = $val;
            }
        }
       }
       //var_dump($row,$new_row);die;
       return $new_row;
    }        
    
}