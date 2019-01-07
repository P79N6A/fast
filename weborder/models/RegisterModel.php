<?php

/**
 * 官网用户注册相关业务
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');
class RegisterModel extends TbModel {

    function get_table() {
        return 'osp_kehu';
    }
    /*
     * 官网注册客户信息
     */
    function insert($client) {
        $ret = $this->is_exists($client['kh_name'], 'kh_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');
        $ret = $this->is_exists($client['kh_code'], 'kh_code');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('code_is_exist');
        $ret = $this->is_exists($client['kh_email'], 'kh_email');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('email_is_exist');
        $ret=parent::insert($client);
        //注册成功发送邮件
        if($ret['status']=1){
            //注册成功后发送一封激活邮件+++++
            //1)生成激活码
            //$key = $this->make_activation_key($client['kh_code']);
            //2)发送邮件
            //$this->send_reg_email($client['kh_email'], $key);
        }
        return $ret;
    }
    
    /**
     * 根据商户代码，生成激活帐号的key，有效期10分钟
     * @author wsc <wsc@baisonmail.com>
     * @date 2015-03-24
     * @param string $renter_code 用户代码
     * @return string 返回生成的key
     */
    public function make_activation_key($renter_code){
        $data = array();
        $data['kh_code'] = $renter_code;
        $data['timestamp'] = time() + 600;
        $data_str = gzcompress(serialize($data), 9);
        $key = urlencode(base64url_encode(mcrypt_encrypt(MCRYPT_3DES, APP_SALT, $data_str, "ecb")));
        return $key;
    }
    
    /**
     * 发送激活邮件
     * @author wsc <wsc@baisonmail.com>
     * @date 2015-03-24
     * @param string $email 邮箱地址
     * @param string $key 激活码
     */
    public function send_reg_email($email,$key){
        $url=CTX()->get_app_conf('$mailserpath');
        if($url!=""){
            $url = $url.$key;
            $body = '请点击以下链接地址激活您的帐号： ';
            $body .= '<a href="'.$url.'" target="_blank">';
            $body .= $url;
            $body .= '</a>';

            require_lib('net/MailEx');
            $mail = new MailEx();
            $mail->setHtml(true)->notify_mail($email, '请激活您的翼商在线订购企业帐号', $body);
        }
        return;
    }

    //验证数据是否重复
    private function is_exists($value, $field_name = 'kh_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    
    
    /**
    * 用户登录验证
    * @param type $username
    * @param type $password
    */
    function checklogOn($userinfo,$username, $password,$captcha){
        if($captcha!=""){
            if(CTX()->get_session("captcha_code")!=strtolower($captcha)){
                return $this->format_ret("-1", "", 'CaptchaError'); //验证码错误
            }
        }
        if(!empty($userinfo["data"])){
            $strSQL="select * from osp_kehu where kh_code=:kh_code and kh_login_pwd=:kh_login_pwd";
            $retRow=$this->db->get_row($strSQL,array(":kh_code"=>$username,":kh_login_pwd"=>$password));
            if(!empty($retRow)){
                CTX()->set_session("LoginState",true); // 登录状态
                CTX()->set_session("kh_id",$retRow['kh_id']); //登录客户ID
                CTX()->set_session("user_id",$retRow['kh_id']); //登录客户ID
                CTX()->set_session("kh_code",$retRow['kh_code']); // 客户代码
                CTX()->set_session("kh_name",$retRow['kh_name']); // 客户名称
                return $this->format_ret("1",array('kh_id'=>$retRow['kh_id'],'kh_code'=>$retRow['kh_code'],'kh_name'=>$retRow['kh_name']), 'success');
            }else{
                //密码错误
                return $this->format_ret("-1", "", 'PwdError');
            }
        }else{
            //用户不存在
            return $this->format_ret("-1", "", 'UserNotFind');
        }
    }
    
    //获取客户信息
    function get_user_info($user) {
        $params = array('kh_code' => $user);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
    
    
    //获取root用户密码
    function get_user_pwd2($user) {
        $ret = $this->get_row(array('kh_code' => $user));
        if (isset($ret)) {
            return $ret['data'];
        } else {
            return "";
        }
    }
    
    
}
