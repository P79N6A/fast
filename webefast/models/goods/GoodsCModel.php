<?php
require_model('common/BaseCModel',false);

class GoodsCModel extends BaseCModel {


    static $s_godos_data = array();

    function __construct() {
        parent::__construct();
    }

    function get_goods_info($goods_code,$key_arr=array()) {
        $info = array();
        if (isset(self::$s_godos_data[$goods_code])) { //内存临时缓存
            $info = &self::$s_godos_data[$goods_code];
        } else {
            $cache_key = $this->get_goods_key($goods_code); //缓存
            $info = $this->get_cache($cache_key);
            if (empty($info)) {
                $info = $this->get_goods_by_goods_code($goods_code);
            }
            $this->set_goods_cache_task();
            self::$s_godos_data[$goods_code] = $info;
        }
        if(!empty($key_arr)){
            $info = $this->get_keys_info($info,$key_arr);
        }
        
        return $info;
    }
    
    
    

    public function get_goods_by_goods_code($goods_code) {
        
        
        
        
        $sql = "select * from base_goods where goods_code=:goods_code";
        $row = $this->db->get_row($sql, array(':goods_code' => $goods_code));
        return $row;
    }

    private function get_goods_key($goods_code) {
        return 'goods/' . md5($goods_code);
    }


    
    function set_goods_cache($goods_code){
        $info =  $this->get_goods_by_goods_code($goods_code);
        $cache_key = $this->get_goods_key($goods_code); //缓存
        $this->set_cache($cache_key, $info);
    } 
    
    function set_goods_cache_task(){
        
    }
 
}
