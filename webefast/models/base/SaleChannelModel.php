<?php
/**
* 销售渠道相关业务
*
* @author huanghy
*/
require_model('tb/TbModel');
require_lang('sys,base');

class SaleChannelModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this -> get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'base_sale_channel';
    }

    /**
    * 根据条件查询数据
    */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] != '') {
            $sql_main .= " AND sale_channel_code LIKE :sale_channel_code";
            $sql_values[':sale_channel_code'] = '%'.$filter['sale_channel_code'].'%';
        }

        if (isset($filter['sale_channel_name']) && $filter['sale_channel_name'] != '') {
            $sql_main .= " AND sale_channel_name LIKE :sale_channel_name";
            $sql_values[':sale_channel_name'] = '%'.$filter['sale_channel_name'].'%';
        }
        $sql_main .=$this->get_values_where();
        $select = '*';
 
        $data = $this -> get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        foreach($ret_data['data'] as $k => $row) {
            $ret_data['data'][$k]['is_system_txt'] = $row['is_system'] == 1 ? '系统定义' : '自定义';
        }

        return $this -> format_ret($ret_status, $ret_data);
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this -> format_ret('error_params');
        }
        $ret = parent :: update(array('is_active' => $active), array('sale_channel_id' => $id));
        return $ret;
    }

    /**
    * 添加新纪录
    */
    function insert($sale_channel) {
        $status = $this -> valid($sale_channel);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> is_exists($sale_channel['sale_channel_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(SALECHANNEL_ERROR_UNIQUE_CODE);

        $ret = $this -> is_exists($sale_channel['sale_channel_name'], 'sale_channel_name');
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(SALECHANNEL_ERROR_UNIQUE_NAME);

        return parent :: insert($sale_channel);
    }
    /**
    * 
    */
    function get_short_code($code) {
        $sql_values[':sale_channel_code'] = $code;
        $sql = "select short_code from base_sale_channel where sale_channel_code = :sale_channel_code ";
        return $this->db->get_value($sql,$sql_values);
    }
    /**
    * 修改纪录
    */
    function update($sale_channel, $id) {
        $status = $this -> valid($sale_channel, true);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> get_row(array('sale_channel_id' => $id));
        if ($sale_channel['sale_channel_name'] != $ret['data']['sale_channel_name']) {
            $ret = $this -> is_exists($sale_channel['sale_channel_name'], 'sale_channel_name');
            if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(SaleChannel_ERROR_UNIQUE_NAME);
        }
        $ret = parent :: update($sale_channel, array('sale_channel_id' => $id));
        return $ret;
    }

    /**
    * 删除纪录
    */
    function delete($id) {
        $ret = parent :: delete(array('sale_channel_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'sale_channel_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }

    function get_data_map(){
      if(!isset($this->data_map)){
        $where  = $this->get_values_where();
        $sql = "select sale_channel_code,sale_channel_name from base_sale_channel WHERE 1 {$where}";
        $arr = $this->db->getAll($sql);
        $this->data_map = array();
        foreach($arr as $sub_arr){
          $this->data_map[$sub_arr['sale_channel_code']] = $sub_arr['sale_channel_name'];
        }
      }
      return $this->data_map;
    }
    
    function get_data_code_map(){
      if(!isset($this->data_code_map)){
        $where  = $this->get_values_where();
        
        $sql = "select sale_channel_id,sale_channel_code,sale_channel_name from base_sale_channel WHERE 1 {$where}";
   
        $arr = $this->db->getAll($sql);
        $this->data_code_map = array();
        //$this->data_code_map[0] = array('0'=>'','1'=>' ');
        foreach($arr as $sub_arr){
          $this->data_code_map[$sub_arr['sale_channel_code']] = array('0'=>$sub_arr['sale_channel_code'],'1'=>$sub_arr['sale_channel_name']);
        }
      }
   
      return $this->data_code_map;
    }
    function get_values_where($field='sale_channel_code'){
        $where  = "";
        $arr = $this->get_no_values();
        if(!empty($arr)){
          $values_str = "'".implode("','", $arr)."'";
          $where = " AND {$field} not in({$values_str})"; 
        }
        return $where;
    }
    function get_no_values(){
            require_model('common/ServiceModel');//正在服务
            $serviceModel = new ServiceModel();
            return $serviceModel->get_value_no_auth(2);
    }
    
    function get_select(){
        $where = $this->get_values_where();
        $sql = " select sale_channel_code,sale_channel_name from {$this->table} where is_active=1  ".$where;
        $data = $this->db->get_all($sql);
        $arr = array();
        foreach($data as $val){
            $arr[] = array($val['sale_channel_code'],$val['sale_channel_name']);
        }
        return $arr;
    }
    //获取有权限的店铺类型
    function get_my_select(){
        $data = load_model('base/ShopModel')->get_purview_shop('sale_channel_code');
        $code = array_column($data,'sale_channel_code');
        $sale_channel_code = $this->arr_to_in_sql_value($code,'sale_channel_code',$sql_values);
        $bs_sql = "select sale_channel_code,sale_channel_name from base_sale_channel where sale_channel_code in ({$sale_channel_code})";
        $data = $this->db->get_all($bs_sql,$sql_values);
        $arr = array();
        foreach($data as $val){
            $arr[] = array($val['sale_channel_code'],$val['sale_channel_name']);
        }
        return $arr;

    }
    
      function get_all_select(){
        $where = $this->get_values_where();
        $sql = " select sale_channel_code,sale_channel_name from {$this->table} where is_active=1  ".$where;
        $data = $this->db->get_all($sql);
        $arr = array();
        foreach($data as $val){
            $arr[] = array($val['sale_channel_code'],$val['sale_channel_name']);
        }
        $arr2[]=array('select_all','全部');
        foreach($arr as $val){
            $arr2[]=$val;
        }
        return $arr2;      
    }  
    
    /**
     * 获取销售平台树列表，供库存策略使用
     */
    function get_nodes() {
        $sql = "SELECT sale_channel_name,sale_channel_code FROM {$this->table}";
        $rs = $this->db->get_all($sql);
        $data = array();
        foreach ($rs as $k => $v) {
            $data[$k]['id'] = $v['sale_channel_code'];
            $data[$k]['text'] = $v['sale_channel_name'];
        }
        return $data;
    }
    
    /**
     * 获取erp日报店铺选择框销售平台树
     */
    function get_erp_api_nodes() {
        $erp_sql = "SELECT sale_channel_code FROM base_shop AS base, mid_api_join AS mid WHERE base.shop_code=mid.join_sys_code AND param_val1=2 AND join_sys_type=0";
        $sale_channel_code_data = $this->db->get_all($erp_sql);
        $sale_channel_code_column = array_column($sale_channel_code_data, 'sale_channel_code');
        $sale_channel_code_str = $this->arr_to_in_sql_value($sale_channel_code_column, 'sale_channel_code' , $sql_values);
        $sql = "SELECT sale_channel_name,sale_channel_code FROM {$this->table} WHERE sale_channel_code IN ({$sale_channel_code_str})";
        $rs = $this->db->get_all($sql, $sql_values);
        $data = array();
        foreach ($rs as $k => $v) {
            $data[$k]['id'] = $v['sale_channel_code'];
            $data[$k]['text'] = $v['sale_channel_name'];
        }
        return $data;
    }

}
