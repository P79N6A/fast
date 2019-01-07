<?php

/**
 * Description of LoginCheckModel
 *
 * @author wq
 */
class LoginCheckModel {

    private $is_strong_safe = false;
    private $login_log = array();
    private $db;
    private $app_key = '';
    private $user_tel = '';

    function __construct() {
        $this->is_strong_safe = CTX()->get_app_conf('is_strong_safe');
        $this->db = CTX()->db;
        $this->set_app_key('23272446');
    }

    function set_login_kh_info($user_code) {
        if (!$this->is_strong_safe) {
            return true;
        }

        //23272446

        $this->login_log['userId'] = $user_code;
        $this->login_log['tid'] = $this->login_log['userId'];
    }

    function set_login_safe($ret, $user_code) {
        if (!$this->is_strong_safe) {
            return true;
        }

        $this->set_login_kh_info($user_code);
        $this->add_login_log($ret);
        if ($ret['status'] == 1) {
            $this->user_tel = $ret['data'];
            $this->check_compute_risk();
        }
    }

    private function add_login_log($ret) {

        $this->login_log['loginResult'] = ($ret['status'] == 1) ? 'success' : 'fail';
        $this->login_log['loginMessage'] = ($ret['status'] == 1) ? '' : $ret['message'];
        $this->login_log['app_key'] = $this->app_key;
        load_model("common/TBSafeModel")->add_login_log($this->login_log);
    }

    private function check_compute_risk() {


        $risk_max = 0.5;
        $param['userId'] = $this->login_log['userId'];
        $ret = load_model("common/TBSafeModel")->compute_risk($param);

        if (isset($ret['result']) && $ret['result'] == 'success') {
            if ($ret['risk'] > $risk_max) { //超过风险值
                $this->set_login_info();
                return $this->get_verify_url();
            }
        }
        return true;
    }

    //     CTX()->set_session("IsLogin",true); // 登录状态

    private function set_login_info() {
        CTX()->set_session("IsLogin", false); // 登录状态
    }

    private function get_verify_url() {
        //  $tel = CTX()->db->get_value("select kh_tel from osp_kehu where kh_id='{$this->kh_id}' ");

        $param['sessionId'] = session_id();
        $param['mobile'] = !empty($this->user_tel)?$this->user_tel:'13482155297'; //登录效验手机
        $param['userId'] = $this->login_log['userId'];
        $param['redirectURL'] = $this->get_redirect_url();


        $ret = load_model("common/TBSafeModel")->get_verify_url($param);


        if (isset($ret['result']) && $ret['result'] == 'success') {
            $ret = array('status' => 10, 'data' => $ret['verifyUrl']);
            echo json_encode($ret);
            die;
        }
        return true;
    }

    function is_verify_pssed($token) {

        $param['token'] = $token;
        $ret = load_model("common/TBSafeModel")->is_verify_pssed($param);
        if (isset($ret['result']) && $ret['result'] == 'success') {
            if ($ret['verifyResult'] == 'success') {
                //跳转到应用
                CTX()->redirect('index/do_index');
                return;
            }
        }
        //重新跳转登录
        CTX()->redirect('login/init_login');
        return;
    }

    private function get_redirect_url() {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        $url.= "?app_act=login/login_safe_check";
        return $url;
    }

    function set_app_key($app_key) {
        $this->app_key = $app_key;
    }

}
