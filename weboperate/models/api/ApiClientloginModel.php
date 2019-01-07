<?php
/**
 * 客户用户相关业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');

class ApiClientloginModel extends TbModel {
    
    function get_table() {
        return 'osp_kehu_login';
    }
    
    /*
     * 插入客户用户信息
     */
    function addclogin($data) {
        //解析request
        return parent::insert($data);
    }
    
    /*
     * 修改客户用户信息
     */
    function editclogin($data,$fiter){
        //解析request
        return parent::update($data,$fiter);
    }
    
    function is_exists($value, $field_name = 'user_id') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

	/**
	 * 通过user_code和renter_id获取信息
	 * @param $user_code
	 * @param $renter_id
	 * @param string $cols
	 */
	function get_info_by_user_code_and_kh_id($user_code, $kh_id) {
		$ret = parent::get_row(array('user_code' => $user_code, 'kh_id'=>$kh_id));
		return $ret;
	}

	/**
	 * 获取
	 * @param $kh_id
	 * @return array
	 */
	function get_list_by_kh_id($kh_id) {

		$ret = parent::get_all(array('kh_id'=>$kh_id));
		return $ret;
	}
}