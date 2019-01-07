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
    private $kh_id;
    private $app_url = '';
    private $app_key = '';
    private $tel = '';
    private $service_tel = '';

    function __construct() {
        $this->is_strong_safe = CTX()->get_app_conf('is_strong_safe');
        $this->db = CTX()->db;
    }

    function set_login_kh_info($kh_id, $user_code, $app_key) {
        if (!$this->is_strong_safe && !empty($app_key)) {
            $this->kh_id = 0;
            //   $this->set_app_key('23300032');
            $this->login_log['userId'] = '0';
            $this->login_log['tid'] = '不存在用户';
            return true;
        }
        $this->kh_id = $kh_id;
        $this->set_app_key($app_key);

        $sql = "select shop_name,shop_nick from mo_shop  where source='taobao' AND kh_id ='{$kh_id}'";
        $data = $this->db->get_all($sql);
        $arr = array();
        foreach ($data as $val) {
            $arr[] = empty($val['shop_nick']) ? $val['shop_name'] : $val['shop_nick'];
        }

        $this->login_log['userId'] = $kh_id . "_" . $user_code;
        $this->login_log['tid'] = empty($arr) ? $this->login_log['userId'] : implode(",", $arr);
    }

    function set_login_safe($ret, $service_security = '') {
        if (empty($this->app_key) || empty($this->kh_id)) {
            return true;
        }
        $this->add_login_log($ret);
        if ($ret['status'] == 1) {
            $this->app_url = $ret['data'];
            //替换手机号
            if (!empty($service_security)) {
                $this->service_tel = $this->get_mobile_by_service_security($service_security);
            }
            return $this->check_compute_risk();
        }
    }

    private function add_login_log($ret) {
        
        if(empty($this->login_log['userId'])||$this->login_log['userId']=='0'){
            return false;
        }
        $this->login_log['loginResult'] = ($ret['status'] == 1) ? 'success' : 'fail';
        $this->login_log['loginMessage'] = ($ret['status'] == 1) ? '' : $ret['message'];
        $this->login_log['app_key'] = $this->app_key;
        load_model("common/TBSafeModel")->add_login_log($this->login_log);
        return true;
    }

    private function check_compute_risk() {


        $risk_max = 0.5;
        $param['userId'] = $this->login_log['userId'];
        $ret = load_model("common/TBSafeModel")->compute_risk($param);

        if (isset($ret['result']) && $ret['result'] == 'success') {
            if ($ret['risk'] > $risk_max) { //超过风险值
                $this->save_url();
                return $this->get_verify_url();
            }
        }
        return true;
    }

    private function get_verify_url() {

        $param['sessionId'] = session_id();
        if (empty($this->tel)) {
            $tel = CTX()->db->get_value("select kh_tel from osp_kehu where kh_id='{$this->kh_id}' ");
        } else {
            $tel = $this->tel;
        }
        $param['mobile'] = empty($tel) ? '13482155297' : $tel;

        if (!empty($this->service_tel)) {
            $param['mobile'] = $this->service_tel;
        }
        $this->service_tel = '';


        $param['userId'] = $this->login_log['userId'];
        $param['redirectURL'] = $this->get_redirect_url();


        $ret = load_model("common/TBSafeModel")->get_verify_url($param);

        if (isset($ret['result']) && $ret['result'] == 'success') {
            $ret = array('status' => 1, 'data' => $ret['verifyUrl']);
            echo json_encode($ret);

            die;
        }

        return true;
    }

    /**
     * 指定角色
     * 有效
     * @param $user_code
     * @return mixed
     */
    function get_mobile_by_service_security($user_code) {
        $sql = "SELECT r1.user_mobile FROM osp_user AS r1 INNER JOIN sys_user_role AS r2 ON r1.user_id=r2.user_id INNER JOIN sys_role AS r3 ON r2.role_id=r3.role_id
                WHERE r1.user_code=:user_code AND r1.user_active=1 AND r3.role_code='service_security' ";
        $sql_values[':user_code'] = $user_code;
        $mobile = $this->db->get_value($sql, $sql_values);
        return $mobile;
    }


    function is_verify_passed($token, $app_key) {

        $param['token'] = $token;
        $param['app_key'] = $app_key;
        $ret = load_model("common/TBSafeModel")->is_verify_passed($param);
        if (isset($ret['result']) && $ret['result'] == 'success') {
            if ($ret['verifyResult'] == 'success') {
                //跳转到应用
                $url = $this->get_url();
                $url = empty($url) ? $url : 'index/login';
                CTX()->redirect($url);
                return;
            }
        }
        //重新跳转登录
        CTX()->redirect('index/login');
        return;
    }

    private function get_redirect_url() {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        $url.= "?app_act=index/login_safe_check&appkey=" . $this->app_key;
        return $url;
    }

    private function save_url() {
        CTX()->set_session('app_url', $this->app_url);
    }

    private function get_url() {
        return CTX()->get_session('app_url');
    }

    function set_app_key($app_key) {
        $this->app_key = $app_key;
    }

    function set_user_tel($tel) {
        $this->tel = $tel;
    }

}
