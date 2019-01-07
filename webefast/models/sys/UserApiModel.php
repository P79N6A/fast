<?php

require_model('tb/TbModel');
require_lang('sys');

/**
 * 用户接口相关业务
 * @author WMH
 */
class UserApiModel extends TbModel {

    /**
     * 登录用户校验
     * @author wmh
     * @date 2017-05-15
     * @param array $params 接口参数
     * <pre> 必选 'user_code','password'
     * @return array 操作结果
     */
    public function api_user_verify($params) {
        $key_required = array(
            's' => array('user_code', 'password'),
        );
        $user_data = array();
        $ret_required = valid_assign_array($params, $key_required, $user_data, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }

        $sql = 'SELECT `user_id`,`status` FROM `sys_user` WHERE user_code=:user_code';
        $ret_user = $this->db->get_row($sql, array(':user_code' => $user_data['user_code']));
        if (empty($ret_user)) {
            return $this->format_ret(-10002, '', 'user_not_found');
        }
        if ($ret_user['status'] != 1) {
            return $this->format_ret(-1, '', '该用户已停用');
        }

        //获取用户角色
        $decrypt_data = $this->user_decrypt($user_data['password']);
        $password = load_model('sys/UserModel')->encode_pwd($decrypt_data['userpass']);
        $sql = 'SELECT COUNT(1) FROM `sys_user` WHERE `user_code`=:user_code AND `password`=:password';
        $ret_pass = $this->db->get_value($sql, array(':user_code' => $user_data['user_code'], ':password' => $password));
        if ($ret_pass == 0) {
            return $this->format_ret(-1, '', 'password_invalid');
        }

        $store_list = load_model('base/StoreModel')->api_purview_store_get($ret_user['user_id']);
        $return_data = array(
            'store_list' => $store_list
        );
        return $this->format_ret(1, $return_data, '验证成功');
    }

    private function user_encrypt() {
        $request_params = array(
            'controller' => 'todo',
            'action' => 'read',
            'username' => "bl",
            'userpass' => "a1"
        );

        $private_key = "28e336ac6c9423d946ba02d19c6a2632";

        //encrypt request  
        $enc_request = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $private_key, json_encode($request_params), MCRYPT_MODE_ECB));
        echo "CRYPT:" . $enc_request . "<br/>";
    }

    private function user_decrypt($enc_request) {
        $private_key = "28e336ac6c9423d946ba02d19c6a2632";
        $params = json_decode(trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $private_key, base64_decode($enc_request), MCRYPT_MODE_ECB)), true);
        return $params;
    }

}
