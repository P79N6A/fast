<?php
/**
 * 客户
 *
 * @author jianbin.zheng
 *
 */
require_model('tb/TbModel');

class ApikehuModel extends TbModel {
    public $pk = 'kh_id';
    
    public function get_table() {
        return 'osp_kehu';
    }
    
    public function get_kehu($kh_id) {
        $ret = $this->get_by_pk($kh_id);
        return $ret;
    }
}
