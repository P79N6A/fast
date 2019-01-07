<?php
/**
* 商店 相关业务
*
*/
require_model('tb/TbModel');
require_lang('base');

class ShopInitModel extends TbModel {
//	public $arr_channel = array('9'=>array('0'=>'9','1'=>'淘宝','2'=>'TB'),'13'=>array('0'=>'13','1'=>'京东','2'=>'JD'),                
//        		           '16'=>array('0'=>'16','1'=>'一号店','2'=>'YHD'),'10'=>array('0'=>'10','1'=>'拍拍','2'=>'PP'),
//        		          '14'=> array('0'=>'14','1'=>'亚马逊','2'=>'YMX'));
    function get_table() {
        return 'base_shop_init';
    }
    
    function create_init($shop_code){
        $data['shop_code'] =  $shop_code;
        return $this->insert($data);
        
        
    }
    function save_info($shop_code,$data){
        return $this->update($data, array('shop_code'=>$shop_code));
    }
 
        
    
    
    function get_info($shop_code){
      return  $this->get_row(array('shop_code'=>$shop_code));
    }
    
    function set_load($shop_code,$num,$type=0){
        $key = ($type==0)?'goods_load':'order_load';
        return $this->update(array($key=>$num), array('shop_code'=>$shop_code));
    }
    
}
