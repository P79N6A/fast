<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TaobaoEncryp
 *
 * @author wq
 */
require_lib('apiclient/taobao/top/security/SecurityClient');
require_lib('security/encrypt/EncryptCache');
require_lib('security/encrypt/EncryptAbs');

class TaobaoEncrypt extends EncryptAbs {

    protected $client;
    protected $session;
    protected $type;
    private static $self;
    protected $error = array();
    private $app_key ;
    static function create() {
        if (!self::$self) {
            self::$self = new TaobaoEncrypt();
        }
        return self::$self;
    }

    /*
     * 数据解密
     */

    function decrypt($encryptValue, $secret_param) {
        $this->session = $secret_param['session'];
        $this->error['message'] = null;
        //测试
        //  $this->session = '6101e265f1b0ac46222545ded25b4a3bc71295f3464b411182558410';

        $this->type = $this->get_encrypt_type($secret_param['type']);
        $originalValue = $encryptValue;
        try {
//            $ss = $this->client->isEncryptData($encryptValue, $this->type);
//          //  $this->type ='simple';
//            var_dump('cc',$this->type,$ss);
            if ($this->client->isEncryptData($encryptValue, $this->type)) {
                $originalValue = $this->client->decrypt($encryptValue, $this->type, $this->session);
            }
        } catch (Exception $ex) {

            $this->error['message'] = $ex->getMessage();
        }

        return $originalValue;
    }

    /*
     * 数据解密
     */

    function decrypt_previous($encryptValue, $secret_param) {
        $this->session = $secret_param['session'];
        $this->error['message'] = null;
        //测试
        //   $this->session = '6101e265f1b0ac46222545ded25b4a3bc71295f3464b411182558410';
        $this->type = $this->get_encrypt_type($secret_param['type']);
        $originalValue = $encryptValue;
        try {

            if ($this->client->isEncryptData($encryptValue, $this->type)) {
                $originalValue = $this->client->decryptPrevious($encryptValue, $this->type, $this->session);
            }
        } catch (Exception $ex) {
            $this->error['message'] = $ex->getMessage();
        }


        return $originalValue;
    }

    /*
     * 数据加密
     */

    function encrypt($originalValue, $secret_param) {
        $this->session = $secret_param['session'];
        $this->error['message'] = null;
        //测试
        //    $this->session = '6101e265f1b0ac46222545ded25b4a3bc71295f3464b411182558410';


        $this->type = $this->get_encrypt_type($secret_param['type']);

        try {
            $encryptValue = $this->client->encrypt($originalValue, $this->type, $this->session);
        } catch (Exception $ex) {
            $encryptValue = $originalValue;
            $this->error['message'] = $ex->getMessage();
        }


        return $encryptValue;
    }

    function is_encrypt_data($encryptValue, $type) {

        $c = new TopClient();
        $c->appkey = '';
        $c->secretKey = '';
        $c->gatewayUrl = 'https://eco.taobao.com/router/rest';


        $client = new SecurityClient($c, '');

        $type = $this->get_encrypt_type($type);
        return $client->isEncryptData($encryptValue, $type);
    }

    protected function get_encrypt_type($type) {
        if ($type != 'phone') {
            $type = 'simple';
        }
        //  $type = 'simple';
        return $type;
    }

    function init($encrypt_param) {
        static $client_all  = null;
        
        if(isset($client_all[$encrypt_param['app_key']])){
            $this->client = $client_all[$encrypt_param['app_key']];
            return true;
        }

            $c = new TopClient();
            $c->appkey = $encrypt_param['app_key'];
            $c->secretKey = $encrypt_param['app_secret'];
            // $c->gatewayUrl = 'https://eco.taobao.com/router/rest';
            if ($c->appkey == '1023300032') {

                $c->gatewayUrl = 'https://gw.api.tbsandbox.com/router/rest';
            } else {
                $c->gatewayUrl = 'https://eco.taobao.com/router/rest';
            }

            // $c->gatewayUrl = 'https://gw.api.tbsandbox.com/router/rest';
            //测试
            // $c->appkey = '1012651526';
            // $c->secretKey = 'sandbox693bfb83d095ad559f98f2b07';
            // //$session = '6101701a21788e0e44743d5f1032ccd5276f00ea6a2d9092050695162';
            // $c->gatewayUrl = 'https://gw.api.tbsandbox.com/router/rest';
            // $encrypt_param['randomNum'] = '8zzFs1n3UelH6UddhMdL14gkRgNxMY6ZXyShn7q9EDg=';


            $this->client = new SecurityClient($c, $encrypt_param['randomNum']);
            $cache = new EncryptCache();
            $this->client->setCacheClient($cache);
            $client_all[$encrypt_param['app_key']] = $this->client;
            return true;
    }

    function get_error() {
        //todo:解密错误怎么预警
        if (isset($this->error['message']) && !empty($this->error['message'])) {
            return $this->error['message'];
        }
        return false;
    }

}
