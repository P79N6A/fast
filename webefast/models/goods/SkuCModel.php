<?php

require_model('goods/GoodsCModel');
class SkuCModel extends GoodsCModel{
    static  $s_sku_data;
    function __construct() {
        parent::__construct();
    }

    function get_sku_info($sku,$key_arr=array()) {
        $info =  $this->get_only_sku_info($sku);
    //   var_dump($info);die;
        if(!empty($info)){
           $goods_info = $this->get_goods_info($info['goods_code'] );
         //  var_dump($goods_info,$info);die;
           $info['sell_price'] = ($info['price']==0)||empty($info['price'])?$goods_info['sell_price']:$info['price'];
          // $info['weight'] = empty($info['weight'])?$goods_info['weight']:$info['weight'];
           $info['weight'] = ($info['weight']==0)||empty($info['weight'])?$goods_info['weight']:$info['weight'];
           $info['cost_price'] = ($info['cost_price']==0)||empty($info['cost_price'])?$goods_info['cost_price']:$info['cost_price'];
           $info['category_name']=$goods_info['category_name'];
           $info = array_merge($goods_info,$info);
        }

        if(!empty($key_arr)){
            $info = $this->get_keys_info($info,$key_arr);
        }

        return $info;
    }
    function get_sku_info_by_sku($sku){
        $sql = "select * from goods_sku where sku=:sku ";
        $row = $this->db->get_row($sql,array(':sku'=>$sku));
        return $row;
    }
    
    function get_barcode($sku){
        $info = $this->get_only_sku_info($sku);
       return isset($info['barcode'])?$info['barcode']:'';
    }

    function get_gb_code($sku){
        $info = $this->get_only_sku_info($sku);
        return isset($info['gb_code'])?$info['gb_code']:'';
    }

    function get_only_sku_info($sku){
       if (isset(self::$s_sku_data[$sku])) { //内存临时缓存
            $info = &self::$s_sku_data[$sku];
        } else {
            //缓存
            $cache_key = $this->get_sku_key($sku); //缓存
            $info = $this->get_cache($cache_key);
            if (empty($info)) {
                $info = $this->get_sku_info_by_sku($sku);
            }
            $this->set_sku_cache_task(); 

            self::$s_sku_data[$sku] = $info;
        }  
        return $info;
    }
    private function get_sku_key($sku) {
        return 'sku/'.$sku;
    }
    
    function set_sku_cache($sku){
        $info =  $this->get_sku_info_by_sku($sku);
        $cache_key = $this->get_sku_key($sku); //缓存
        $this->set_cache($cache_key, $info);
    }   
    
    function set_sku_cache_task(){
        
    }
    

  
 
}
