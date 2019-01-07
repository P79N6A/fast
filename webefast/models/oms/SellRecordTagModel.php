<?php
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

/**
 * Description of SellRecordTagModel
 *
 * @author user
 */
class SellRecordTagModel extends TbModel {
    //put your code here
    protected $table = 'oms_sell_record_tag';
    
    function get_list_by_code($filter){
        $sql_main = "FROM {$this->table} rl WHERE rl.sell_record_code=:sell_record_code AND rl.tag_type=:tag_type";
        $sql_values['sell_record_code'] = $filter['sell_record_code'];
        $sql_values['tag_type'] = $filter['tag_type'];
        $select = "*";
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        foreach($data['data'] as &$v){
            $v['tag_name'] = oms_tb_val('base_question_label', 'question_label_name', array('question_label_code' => $v['tag_v']));
        }
        return $this->format_ret(1, $data);
    }
    
    function add_record_tag($sell_record_code,$tag_arr){
        $sql_values = array();
        $tag_str = $this->arr_to_in_sql_value($tag_arr, 'order_label_code', $sql_values);
        $sql = "select order_label_code,order_label_name from base_order_label where order_label_code in({$tag_str}) ";
        $data = $this->db->get_all($sql,$sql_values);
        $tag_data = array();
        $tag_type = 'order_tag';
        $ret = array();
        if(!empty($data)){
                foreach($data as $val){
                    $tag_data[] = array('sell_record_code'=>$sell_record_code,'tag_type'=>$tag_type,
                        'tag_v'=>$val['order_label_code'],'tag_desc'=>$val['order_label_name']);
                 }
              $ret =  $this->insert_multi($tag_data, TRUE);
        }else{
            $ret = $this->format_ret(-1,'','未找到指定标签');
        }
        return $ret;
    }
    function get_sell_record_by_tag($tag_arr,$type='order_tag'){
        $tag_str = "'".implode("','", $tag_arr)."'";
        $sql = "select sell_record_code from {$this->table} where tag_type=:tag_type AND tag_v IN({$tag_str})  ";
        $data = $this->db->get_all($sql,array(':tag_type'=>$type));
        $ret_data = array();
        foreach($data as $val){
            $ret_data[] = $val['sell_record_code'];
        }
        return $ret_data;
    }
    
    function get_tag_by_sell_record($sell_record_arr,$type='order_tag',$select = "*"){
        $sell_record_str = "'".implode("','", $sell_record_arr)."'";
        $sql = "select {$select} from {$this->table} where  tag_type=:tag_type AND sell_record_code in({$sell_record_str}) ";
        $data =  $this->db->get_all($sql,array(':tag_type'=>$type));
        return $this->format_ret(1,$data);
    }
    
    /**
     * 查询订单是否存在对应的问题类型
     * @param string $sell_record_code 订单号
     * @param string $tag_v 问题标签值
     * @return array 查询数据结果
     */
    function is_exists_question($sell_record_code, $tag_v) {
        $sql = "SELECT 1 FROM {$this->table} WHERE tag_v=:tag_v AND sell_record_code=:code";
        $data = $this->db->get_all($sql, array(':tag_v' => $tag_v, ':code' => $sell_record_code));
        return $data;
    }
    
    /**
     * @todo 获取每单的问题数
     */
    function get_sum_by_sell_record_code($sell_record_code){
        $sql = "SELECT count(1) FROM oms_sell_record_tag WHERE sell_record_code = :sell_record_code AND tag_type='problem'";
        $sql_value = array(":sell_record_code" => $sell_record_code);
        $ret = $this->db->get_value($sql, $sql_value);
        return $ret;
    }
    
    /**
     * 获取整单退/部分退的问题单
     */
  
     function get_problem_refund() {
        $sql = "SELECT sell_record_code FROM oms_sell_record_tag WHERE tag_type='problem' AND tag_v IN('REFUND','FULL_REFUND')";
        return $this->db->get_all_col($sql);
    }
        /**
     * 获取整单退
     */
  
     function get_problem_full_refund() {
        $sql = "SELECT sell_record_code FROM oms_sell_record_tag WHERE tag_type='problem' AND tag_v IN('FULL_REFUND')";
        return $this->db->get_all_col($sql);
    }
   /**
     * 获取部分退
     */
     function get_problem_part_refund() {
        $sql = "SELECT sell_record_code FROM oms_sell_record_tag WHERE tag_type='problem' AND tag_v IN('REFUND')";
        return $this->db->get_all_col($sql);
    }
       /**
     * 根据订单查询部分退的单据
     */
     function get_problem_refund_by_record($sell_record_arr) {
        $sell_record_str = "'".implode("','", $sell_record_arr)."'";
        $sql = "SELECT sell_record_code FROM oms_sell_record_tag WHERE tag_type='problem' AND tag_v IN('REFUND')  AND sell_record_code in({$sell_record_str})";
        return $this->db->get_all_col($sql);
    }
   /**
     * 非退的问题单
     */
     function get_problem_no_refund_by_record($sell_record_arr) {
        $sell_record_str = "'".implode("','", $sell_record_arr)."'";
        $sql = "SELECT sell_record_code FROM oms_sell_record_tag WHERE tag_type='problem' AND tag_v NOT IN('REFUND','FULL_REFUND')  AND sell_record_code in({$sell_record_str})";
        return $this->db->get_all_col($sql);
    }
//           /**
//     * 根据订单查询部分退的单据
//     */
//     function get_problem_refund_by_record($sell_record_arr) {
//        $sell_record_str = "'".implode("','", $sell_record_arr)."'";
//        $sql = "SELECT sell_record_code FROM oms_sell_record_tag WHERE tag_type='problem' AND tag_v IN('REFUND')  AND sell_record_code in({$sell_record_str})";
//        return $this->db->get_all_col($sql);
//    }
   /**
     * 全退的问题单
     */
     function get_problem_full_refund_by_record($sell_record_arr) {
        $sell_record_str = "'".implode("','", $sell_record_arr)."'";
        $sql = "SELECT sell_record_code FROM oms_sell_record_tag WHERE tag_type='problem' AND tag_v  IN('FULL_REFUND')  AND sell_record_code in({$sell_record_str})";
        return $this->db->get_all_col($sql);
    }
     /**
     * 包含退的问题单
     */
     function get_problem_have_refund_by_record($sell_record_arr) {
        $sell_record_str = "'".implode("','", $sell_record_arr)."'";
        $sql = "SELECT sell_record_code FROM oms_sell_record_tag WHERE tag_type='problem' AND tag_v  IN('REFUND','FULL_REFUND')  AND sell_record_code in({$sell_record_str})";
        return $this->db->get_all_col($sql);
    }
}
