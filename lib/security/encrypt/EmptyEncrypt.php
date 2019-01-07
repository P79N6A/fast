<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of empytEncryp
 *
 * @author wq
 */
require_lib('security/encrypt/EncryptAbs');

class EmptyEncrypt extends EncryptAbs {

    private static $self;

    static function create() {
        if (!self::$self) {
            self::$self = new EmptyEncrypt();
        }
        return self::$self;
    }

    function decrypt($data, $secret_param) {
        return $data;
    }

    /*
     * 单条数据解密
     */

    function encrypt($data, $secret_param) {
        return $data;
    }

    function init($encryp_param) {
        
    }

    function is_encrypt_data($data, $secret_param) {
        return true;
    }

    function decrypt_previous($data, $secret_param) {
        
    }

}
