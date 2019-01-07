<?php

/**
 * Description of SysEncrypModel
 *
 * @author wq
 */
require_model('tb/TbModel');

class SysEncrypModel extends TbModel {

    function __construct() {
        parent::__construct('sys_encrypt');
    }

    function create_shop_encrypt($shop_code) {

        $sql = "select sale_channel_code,authorize_state from base_shop where shop_code=:shop_code  ";

        $row = $this->db->get_row($sql, array(':shop_code' => $shop_code));
        if ($row['authorize_state'] != 1) {
            return $this->format_ret(-1, '请先进行店铺授权!');
        }


        $sale_channel_code = $row['sale_channel_code'];
        if($sale_channel_code=='fenxiao'){
            $sale_channel_code = 'taobao';
        }
        
        $is_encrypt = load_model('sys/security/CustomersSecurityModel')->is_encrypt_sale_channel($sale_channel_code);
        if ($is_encrypt === false) {
            return $this->format_ret(-2, '暂时只有不支持加密!');
        }


        //先默认为淘宝 ，后台根据店铺类型设置type
        $data = array(
            'shop_code' => $shop_code,
            'type' => $sale_channel_code,
            'start_time' => time(),
            'status' => 1,
        );
        return $this->insert_dup($data);
    }

    function reset_shop_encrypt($shop_code) {
        $param = array(
            'shop_code' => $shop_code,
            'status' => 1,
        );
        $ret_encrypt = $this->get_row($param);
        if (!empty($ret_encrypt['data'])) {
            $data['status'] = 1;
            $data['end_time'] = time();
            $where = " id=" . $ret_encrypt['data']['id'];
            $this->update($data, $where);
            $ret = $this->create_shop_encryp($shop_code);
        } else {
            $ret = $this->format_ret(1, '', '店铺无加密,不需要重置!');
        }
        return $ret;
    }

    function get_shop_encrypt($shop_code) {

        $param = array(
            'shop_code' => $shop_code,
            'status' => 1,
        );
        $ret = $this->get_row($param);
        if (empty($ret['data'])) {
            return $this->format_ret(1, '', '暂时未开启店铺加密!');
        }
        return $ret;
    }

    /**
     * 获取加密方式
     * @param type $shop_code
     * @return array
     */
    function get_encrypt_info_by_shop($shop_code) {
        static $encrypt_info = null;
        if (!isset($encrypt_info[$shop_code])) {
            $ret = $this->get_shop_encrypt($shop_code);
            if(empty($ret['data'])){
                return array();
            }
            
            $encrypt_info[$shop_code] = $ret['data'];
        }
        return $encrypt_info[$shop_code];
    }

    function get_encryp_by_id($id) {
        static $encrypt_info = null;
        if (!isset($encrypt_info[$id])) {
            $param = array(
                'id' => $id,
            );
            $ret = $this->get_row($param);
            $encrypt_info[$id] = $ret['data'];
        }
        return $encrypt_info[$id];
    }

}
