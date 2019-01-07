<?php

/**
 * Description of encrypabs
 *
 * @author wq
 */
abstract class EncryptAbs {
    /*
     * 单条数据加密
     */

    abstract function decrypt($data, $secret_param);

    /*
     * 单条数据解密
     */

    abstract function encrypt($data, $secret_param);

    abstract function init($encryp_param);
    /*
     * 上一版本解密
     */

    abstract function decrypt_previous($data, $secret_param);
    abstract function is_encrypt_data($data, $secret_param);
    
    
}
