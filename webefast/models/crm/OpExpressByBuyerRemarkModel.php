<?php
/**
 *

订单快递适配买家留言
 *
 * @author wq
 *
 */
require_model('tb/TbModel');
require_lang('crm');

class OpExpressByBuyerRemarkModel extends TbModel {
    function get_table() {
        return 'op_express_by_buyer_remark';
    }
    /*
     * 列表
     */
    function get_by_page($filter = array()) {        
                //$this->set_express_by_buyer_remark();
		$sql_main = "FROM {$this->table} oe RIGHT JOIN base_express be  on  oe.express_code = be.express_code  WHERE 1";
		$sql_values = array();

                $sql_main .= " AND be.status=1";

		$select = 'if(oe.key_word is NULL,be.express_name,oe.key_word)  as key_word,be.express_name,be.express_code';
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		$ret_status = OP_SUCCESS;

                
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
    }
    

    
     function save_express_by_buyer_remark($express_code,$key_word){
         
         $express_key_word_arr = $this->get_express_all_key_word();

         $key_word_arr = $this->get_key_word_by_split($key_word);
         foreach($key_word_arr as $key_word_val){
             if(isset($express_key_word_arr[$key_word_val])&&$express_code!=$express_key_word_arr[$key_word_val]){
                    $express_name = $this->db->get_value("select express_name from base_express where express_code=:express_code",array(':express_code'=>$express_key_word_arr[$key_word_val]));
                    return $this->format_ret(-1,array(),'关键字与配送方式 '.$express_name .' 的关键字重复');
             }
         }
         
         
         $data['key_word'] = $key_word;
         $data['express_code'] = $express_code;
         $update_str = "key_word = VALUES(key_word)";
         return $this->insert_multi_duplicate($this->table, array($data),$update_str);
     }
     
     
     function get_express_all_key_word(){
         static  $express_key_word = null;
         if(!empty($express_key_word)){
             return $express_key_word;
         }
         
         
         $sql = "SELECT  oe.key_word,be.express_name,be.express_code  FROM {$this->table} 
         oe RIGHT JOIN base_express be  on  oe.express_code = be.express_code  WHERE 1 AND be.status=1";
         $data = $this->db->getAll($sql);
         foreach($data as $val){
             if(empty($val['key_word'])){
                 $express_key_word[$val['express_name']] = $val['express_code'];
             }else{
                 $key_word_arr = $this->get_key_word_by_split($val['key_word']);
                 foreach($key_word_arr as $key_word){
                      $express_key_word[$key_word] = $val['express_code']; 
                 }
             }
         }
         return $express_key_word;
         
     }


     function get_express_by_buyer_remark($buyer_remark){
        $express_key_word = $this->get_express_all_key_word();
        $find_express_code = '';
        foreach($express_key_word as $key_word=>$express_code){
            if(strpos($buyer_remark,$key_word)!==FALSE){
                $find_express_code = $express_code;
                break;
            }
        }
        return $this->format_ret(1,$find_express_code);
     }
     private function get_key_word_by_split($key_word){
          $key_word = str_replace('，', ',', $key_word);
          $key_word_arr = explode(",", $key_word);
         return $key_word_arr;
     }
     
     
}
?>
