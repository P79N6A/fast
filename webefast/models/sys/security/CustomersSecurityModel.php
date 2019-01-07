<?php

/**
 * Description of CustomersEncrypModel
 *
 * @author wq
 */
require_lib('security/encrypt/DataEncrypt');
require_model('tb/TbModel');

class CustomersSecurityModel extends TbModel {

    protected $client;
    protected $type_encrypt;
    static protected $customer_address_data;

    function __construct() {
        parent::__construct('crm_customer_address_encrypt');
        $this->type_encrypt = array(
            //订单 处理
            'name' => 'simple',
            'buyer_name' => 'simple',
            'address' => 'simple',
            'tel' => 'simple', //phone
            'home_tel' => 'simple',
        );
        $this->client = DataEncrypt::m();
    }

    /**
     * 获取解密数据
     * @param type $customer_address_id
     * @param type $type
     * @return string
     */
    function get_customer_decrypt($customer_address_id, $type) {
        if (!isset($this->type_encrypt[$type])) {
            return false;
        }

        $address_encrypt_row = $this->get_customer_address_encrypt($customer_address_id);
        if (empty($address_encrypt_row)) {
            $addresst_row = $this->get_customer_address_data($customer_address_id);
            return $addresst_row[$type];
        }

        $encrypt_value = $address_encrypt_row[$type];

        $type_val = $this->type_encrypt[$type];

        return $this->decrypt_text($encrypt_value, $type_val, $address_encrypt_row['encrypt_id']);
    }

    /**
     * 加密店铺数据
     * @param type $encrypt_text
     * @param type $type
     * @param type $shop_code
     * @return  string
     */
    function encrypt_shop_value($encrypt_text, $type, $shop_code) {

        $encrypt_row = load_model('sys/security/SysEncrypModel')->get_encrypt_info_by_shop($shop_code);
        $encrypt_value = $encrypt_text;

        if (!empty($encrypt_row)) {
            $encrypt_value = $this->encrypt_value($encrypt_text, $type, $encrypt_row['id']);
        }

        return $encrypt_value;
    }

    /**
     * 获取解密数据
     * @param type $encrypt_value
     * @param type $type
     * @return string
     */
    function decrypt_text($encrypt_value, $type, $encrypt_id) {
        $encrypt_row = load_model('sys/security/SysEncrypModel')->get_encryp_by_id($encrypt_id);
        //phone
        $len = strlen($encrypt_value);
        $start_i = strpos($encrypt_value, '$');
        $end_i = strripos($encrypt_value, '$');

        if ($start_i === 0 && $end_i == ($len - 1)) {//自动识别是否手机号
            $type = 'phone';
        }else{
            $type = 'simple';
        }

        //加密规则已经过期，调用前规则解密
        if ($encrypt_row['status'] < 1) {
            $decrypt_text = $this->decrypt_previous_text($encrypt_value, $type, $encrypt_id);
        } else {
            $decrypt_text = $this->client->decrypt($encrypt_value, $type, $encrypt_id);
        }

        return $decrypt_text;
    }

    /**
     * 获取解密数据
     * @param type $encrypt_value
     * @param type $type
     * @return string
     */
    function decrypt_shop_text($encrypt_value, $type, $shop_code) {
        $encrypt_row = load_model('sys/security/SysEncrypModel')->get_encrypt_info_by_shop($shop_code);
        if (empty($encrypt_row)) {
            return false;
        }
        //加密规则已经过期，调用前规则解密
        return $this->decrypt_text($encrypt_value, $type, $encrypt_row['id']);
    }

    /**
     * 获取解密数据
     * @param type $encrypt_value
     * @param type $type
     * @return string
     */
    function decrypt_previous_text($encrypt_value, $type, $encrypt_id) {
        return $this->client->decrypt_previous($encrypt_value, $type, $encrypt_id);
    }

    /**
     * 加密数据
     * @param type $encryp_text
     * @param type $type
     * @param type $encryp_id
     * @return string
     */
    function encrypt_value($encryp_text, $type, $encryp_id) {

        if ($type == 'phone') {//自动识别是否手机号
            $type = 'simple';
        }

        $encrypt_value = $this->client->encrypt($encryp_text, $type, $encryp_id);

        return $encrypt_value;
    }

    /*
     * 获取用户加密数据
     */

    function get_customer_address_encrypt($customer_address_id) {

        if (!isset(self::$customer_address_data[$customer_address_id])) {
            $params['customer_address_id'] = $customer_address_id;
            $ret = $this->get_row($params);
            if (empty($ret['data'])) {
                return array();
            }

            self::$customer_address_data[$customer_address_id] = $ret['data'];
        }
        return self::$customer_address_data[$customer_address_id];
    }

    function set_customer_address_data($customer_address_id_arr) {
        //批量情况，清空防止内存溢出
        self::$customer_address_data = array();
        if (!empty($customer_address_id_arr)) {
            $customer_address_id_str = implode(",", $customer_address_id_arr);
            $sql = "SELECT * from crm_customer_address_encrypt where customer_address_id in({$customer_address_id_str}) ";
            $data = $this->db->get_all($sql);
            foreach ($data as $val) {
                self::$customer_address_data[$val['customer_address_id']] = $val;
            }
        }
    }

    function is_encrypt_value($text, $encryp_type, $type) {
        $len = strlen($text);
        $start_i = strpos($text, '$');
        $end_i = strripos($text, '$');
        if ($start_i === 0 && $end_i == ($len - 1)) {//自动识别是否手机号
            $type = 'phone';
        }
        
        
        return $this->client->is_encrypt_value($text, $encryp_type, $type);
    }

    function is_encrypt_address($customer_address_id) {
        $sql = "select id from crm_customer_address_encrypt where customer_address_id=:customer_address_id ";
        $num = $this->db->get_value($sql, array(':customer_address_id' => $customer_address_id));
        if ($num > 0) {
            return true;
        }
        return false;
    }

    function is_encrypt_sale_channel($sale_channel_code) {
        $encrypt_sale_channel = array(
            'taobao'
        );
        if (in_array($sale_channel_code, $encrypt_sale_channel)) {
            return true;
        }
        return false;
    }

    function get_customer_address_data($customer_address_id) {
        $sql = "select * from crm_customer_address where customer_address_id=:customer_address_id";
        $row = $this->db->get_row($sql, array(':customer_address_id' => $customer_address_id));
        $row['buyer_name'] = $this->db->get_value("select customer_name from crm_customer where customer_code=:customer_code" ,array(':customer_code'=>$row['customer_code']));
        return $row;
    }

    function get_encrypt_value_all($text) {
        $encrypt_data = array();
        $data = $this->db->get_all("select id from sys_encrypt where status=1");
        foreach ($data as $val) {
            $encrypt_data[] = $this->encrypt_value($text, 'simple', $val['id']);
        }
        return $encrypt_data;
    }

}
