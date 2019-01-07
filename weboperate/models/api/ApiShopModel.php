<?php
/**
 * 店铺相关业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');

class ApiShopModel extends TbModel {
    
    function get_table() {
        return 'osp_shangdian';
    }
    
    /*
     * 插入店铺信息
     */
    function addshop($data) {
        //解析request
        return parent::insert($data);
    }
    
    /*
     * 修改店铺信息
     */
    function editshop($data,$fiter){
        //解析request
        $ret = parent::update($data,$fiter);
        return $ret;
    }
    
    function is_exists($value, $field_name = 'sd_id') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
}