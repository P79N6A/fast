<?php
/**
 * 短信模板 相关业务
 *
 * @author dfr
 */
require_model('tb/TbModel');


class SysAuthModel extends TbModel {
    private $pra_product_area_map = array();
    function __construct() {
        parent::__construct();
            $this->pra_product_area_map = array('1'=>'online','2'=>'offline','3'=>'o2o');  
    }
            
    function get_table() {
        return 'sys_auth';
    }
    function get_cp_code(){
        static   $ret_data = array();
        if(empty($ret_data)){
            $ret = $this->get_row(array('code'=>'cp_code'));

            if(empty($ret['data']['value'])){
                $ret_data = 'efast5_Standard';
            }else{
                  $ret_data =trim($ret['data']['value']);
            }
        }
        return $this->format_ret(1,$ret_data);
    }
     function get_cp_area(){
        $ret = $this->get_row(array('code'=>'cp_area'));
        $ret_data = 'online';
        if(!empty($ret['data']['value'])){
              $ret_data =trim($ret['data']['value']);
        }      
        if(!in_array($ret_data,  $this->pra_product_area_map )){
             $ret_data = 'online';
        }
 
        return $this->format_ret(1,$ret_data);
    }   
    
    
    function product_version_no(){
        $ret = $this->get_row(array('code'=>'cp_code'));
            $version_no = 'efast365_standard';
        if(!empty($ret['data']['value'])){
              $version_no = strtolower(trim($ret['data']['value'])) ;
        }
        
         $product_version_map = array('0'=>'efast5_standard','1'=>'efast5_enterprise','2'=>'efast5_ultimate');
         $product_version_map_new = array('0'=>'efast365_standard','1'=>'efast365_enterprise','2'=>'efast365_ultimate');
         
         $no = array_search($version_no, $product_version_map);
         if($no===false){
              $no = array_search($version_no, $product_version_map_new);
         }
         if($no===false){
             $no = 0;
         }
         
         return $no;
    }
    function get_auth(){
        static $result = NULL;
        if(is_null($result)){
            $sql = "select code,value from sys_auth";
            $arr = ctx()->db->get_all($sql);
            $result = load_model('util/ViewUtilModel')->get_map_arr($arr,'code',0,'value');
        }
        return $result;
    }
    
    
    private function get_auth_time(){
        
           $sql = "select value from sys_auth where code=:code AND name=:name";
           $user_name = CTX()->get_session('user_code',true);
           $sql_value = array(':code'=>'auth_shop_time',':name'=>$user_name);

           $auth_shop_time = $this->db->get_value($sql,$sql_value);
   
           if(empty($auth_shop_time)){
                $auth_shop_time = 0;
           }
           return $auth_shop_time;
    }
    
    function check_is_auth(){
           return   $this->format_ret(1);
        $is_strong_safe = CTX()->get_app_conf('is_strong_safe');
        $no_check_arr = array('2257'); //演示帐号
        $kh_id = CTX()->saas->get_saas_key();
        
        if(!$is_strong_safe||in_array($kh_id, $no_check_arr)){
              return   $this->format_ret(1);
        }
        $time = time();
        $auth_time = $this->get_auth_time();
    
        if(($time-$auth_time)>600){
           $sql = "select shop_code from base_shop where  authorize_state='1'  and sale_channel_code='taobao'  ";
           $shop_code  = $this->db->get_value($sql);
           if(!empty($shop_code)){
               $url = "?app_act=base/shop/pre_authorize&shop_code=".urlencode($shop_code);
               $msg = "重置密码需要淘宝二次授权，请授权后重置密码";
         
               return  $this->format_ret(-10,$url,$msg);
           }
        }
        
        return   $this->format_ret(1);
        
    }
    
}
?>
