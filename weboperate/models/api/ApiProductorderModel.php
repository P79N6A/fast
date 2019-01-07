<?php
/**
 * 客户产品订购业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');

class ApiProductorderModel extends TbModel {
    
    function get_table() {
        return 'osp_productorder';
    }
    
    /*
     * 插入客户信息
     */
    function addporder($data) {
        //解析request
        return parent::insert($data);
    }
    
    /*
     * 修改客户信息
     */
    function editporder($data,$fiter){
        //解析request
        return parent::update($data,$fiter);
    }
    
    function is_exists($value, $field_name = 'id') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
}