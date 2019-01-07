<?php

class DataEncrypt {

    protected $db;
    protected $Client;
    private static $self;

    function __construct($db = null) {
        $this->db = empty($db) ? CTX()->db : $db;
    }

    static function m() {
        if (!self::$self) {
            self::$self = new DataEncrypt();
        }
        return self::$self;
    }

    function decrypt($data, $type, $encrypt_id) {
        $secret_param = $this->get_secret_param($encrypt_id);
        $secret_param['type'] = $type;

        $decrypt_data = $this->Client->decrypt($data, $secret_param);
        $error_message = $this->Client->get_error();
        if ($error_message !== false) {
            return false;
        }

        return $decrypt_data;
    }

    function encrypt($data, $type, $encrypt_id) {
        $secret_param = $this->get_secret_param($encrypt_id);
        $secret_param['type'] = $type;

        $encrypt_data = $this->Client->encrypt($data, $secret_param);
        $error_message = $this->Client->get_error();
        if ($error_message !== false) {
            return false;
        }
        return $encrypt_data;
    }

    function is_encrypt_value($text, $encryp_type, $type) {
        if ($encryp_type == 'fenxiao') {
            $encryp_type = 'taobao';
        }
        $EncrypClsName = ucfirst($encryp_type) . "Encrypt";
        require_lib("security/encrypt/" . $EncrypClsName);
        $Client = $EncrypClsName::create();

        return $Client->is_encrypt_data($text, $type);
    }

    function decrypt_previous($data, $type, $encrypt_id) {
        $secret_param = $this->get_secret_param($encrypt_id);
        $secret_param['type'] = $type;
        $decrypt_data = $this->Client->decrypt_previous($data, $secret_param);
        $error_message = $this->Client->get_error();
        if ($error_message !== false) {
            return false;
        }
        return $decrypt_data;
    }

    function get_secret_param($encrypt_id) {
        static $ClientAll = NULL;
        static $secret_param_all = NULL;


        if (!isset($ClientAll[$encrypt_id])) {
            $encrypt_type_data = $this->get_encrypt_type_data($encrypt_id);
            $EncrypClsName = ucfirst($encrypt_type_data['type']) . "Encrypt";
            require_lib("security/encrypt/" . $EncrypClsName);
            $ClientAll[$encrypt_id] = $EncrypClsName::create();

            $this->Client = $ClientAll[$encrypt_id];
            $api_data = $this->get_shop_api_info($encrypt_type_data['shop_code']);

            if ($encrypt_type_data['type'] == 'taobao') {
                $app_info = require_conf('sys/app_info');
                $encrypt_param = array(
                    'app_key' => $api_data['app_key'],
                    'app_secret' => $api_data['app_secret'],
                    'session'=>$api_data['session'],
                    'randomNum'=>$app_info['taobao'][$api_data['app_key']]['randomNum'],
                );
                $secret_param_all[$encrypt_id]= $encrypt_param;
    
                //测试
                // $encrypt_param['randomNum'] = 'S7/xdg4AD7WooWY7+g11qoBpaVsEkonULDJPEiMcXPE=';
                $this->Client->init($encrypt_param);
            }
        }
        $this->Client = $ClientAll[$encrypt_id];
        $this->Client->init($secret_param_all[$encrypt_id]);
        

        return $secret_param_all[$encrypt_id];
    }

    protected function get_encrypt_type_data($encrypt_id) {
        static $encrypt_type_data = array();
        if ($encrypt_id == 0) {
            return array('type' => 'empty');
        }
        if (!isset($encrypt_type_data[$encrypt_id])) {
//            $sql = "select * from sys_encryp  where id=:id";
//            $encrypt_type_data[$encrypt_id] = $this->db->get_row($sql, array(':id' => $encrypt_id));

            $encrypt_type_data[$encrypt_id] = load_model('sys/security/SysEncrypModel')->get_encryp_by_id($encrypt_id);
        }
        return isset($encrypt_type_data[$encrypt_id]) ? $encrypt_type_data[$encrypt_id] : array('type' => 'empty');
    }

    protected function get_shop_api_info($shop_code) {
        $sql = "select api from base_shop_api where shop_code=:shop_code ";
        $api_txt = $this->db->get_value($sql, array(':shop_code' => $shop_code));
        $api_data = array();
        if (!empty($api_txt)) {
            $api_data = json_decode($api_txt, true);
        }
        return $api_data;
    }

}
