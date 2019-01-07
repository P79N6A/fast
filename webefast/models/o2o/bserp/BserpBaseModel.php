<?php
require_model('tb/TbModel');
class BserpBaseModel extends TbModel {
    public $o2o_conf = array();
    public $erp_config = array();
    function __construct() {
        parent::__construct();
    }
    
    function check_is_o2o_store($store_code){
        $sql = "select o2o_store from sys_api_shop_store where p_type=0 and shop_store_type=1 and shop_store_code=:shop_store_code";
        $o2o_store = $this->db->getOne($sql,array(':shop_store_code'=>$store_code));
        if($o2o_store == 1){
            return true;
        }
        return false;
        
    }
    
    function get_api_param($store_code){
        if (!isset($this->erp_config[$store_code])){
            $sql = "select c.* from sys_api_shop_store as s,erp_config as c where s.p_type=0 and s.shop_store_type=1 and s.shop_store_code=:shop_store_code and s.p_id=c.erp_config_id";
            $erp_config = $this->db->get_row($sql,array(':shop_store_code'=>$store_code));
            $this->erp_config[$store_code] = $erp_config;
        
        }
        return $this->erp_config[$store_code];
    }
    
    function biz_req($method,$params,$store_code){

        $mod = $this->get_api_mod($store_code);
        if($mod===false){
              return $this->format_ret(-1,'','参数异常');
        }
        $ret = $mod->request_send($method, $params);

        return $ret; 
    }
    function get_api_mod($store_code){
        $erp_config = $this->get_api_param($store_code);
        $r = require_model('o2o/bserp/BserpApiModel');
        $M = new BserpApiModel($erp_config);
        
        return $M;
    }
    function get_store_outside_code($store_code){
        $sql = "select outside_code from sys_api_shop_store where p_type=0 and shop_store_type=1 and shop_store_code=:shop_store_code and outside_type=1";
        $outside_code = $this->db->getOne($sql,array(':shop_store_code'=>$store_code));
        return $outside_code;
    }
    function get_shop_outside_code($shop_code){
        $sql = "select outside_code from sys_api_shop_store where p_type=0 and shop_store_type=0 and shop_store_code=:shop_store_code and outside_type=0";
        $outside_code = $this->db->getOne($sql,array(':shop_store_code'=>$shop_code));
        return $outside_code;
    }
    function is_canceled($record_code,$record_type='sell_record'){
        
        $sql = "select new_record_code from o2o_oms_trade where record_code = :record_code and record_type = :record_type";
        $new_record_code = $this->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
        return !empty($new_record_code)?$new_record_code:$record_code;
    }
    
   
}
