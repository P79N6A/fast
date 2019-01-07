<?php
/**
* efast5.0登录
*
* @author WangShouChong
*/
require_model('tb/TbModel');
require_lang('sys');

class EfastloginModel extends TbModel {
    function get_table() {
        return 'osp_productorder_auth';
    }
    
    function getauthstate($kh_code) {
        //匹配客户档案
        $sql_main="SELECT * FROM osp_kehu WHERE kh_code=:kh_code "; 
        $sql_values[':kh_code'] = $kh_code; //tzkh001
        $ret=$this->db->get_row($sql_main, $sql_values);
        if($ret){
            //获取客户关联产品授权信息
            $params = array('pra_kh_id' => $ret['kh_id'],'pra_cp_id'=>'18');
            $ret = $this->get_row($params);
            if($ret['data']){
                if($ret['data']['pra_state']=='0')
                    return $this->format_ret("-1", "", '产品未授权');
                if(date("Y-m-d",strtotime($ret['data']['pra_enddate']))<date("Y-m-d"))
                    return $this->format_ret("-1", "", '授权已过期');
                return $ret;
            }else{
                return $this->format_ret("-1", "", '产品未授权');
            }
        }else{
            return $this->format_ret("-1", "", '客户不存在');
        }
    }

    function login_efast($user) {
        if (empty($user['customer_code'])){
            return $this->format_ret(-1,'','请填写商户代码');
        }
        if (empty($user['user_code'])){
            return $this->format_ret(-1,'','请填写用户名');
        }
        if (empty($user['password'])){
            return $this->format_ret(-1,'','请填写密码');
        }                
        $ret = $this -> getauthstate($user['customer_code']);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $efast_url = $ret['data']['pra_serverpath'];
        //echo '<hr/>$ret<xmp>'.var_export($efast_url,true).'</xmp>';die;
        //$efast_url = "http://192.168.164.40/webefast/web/";
        $timestamp = time();
        $secret = '4rXm3QQW30Y4EkVEiuJ5CapgAG$xXsh$';
        $params = array();
        require_model('common/CryptModel');
        $crypt_mdl = new CryptModel($timestamp);
        $params['user_code'] = $crypt_mdl -> ecb_encrypt($user['user_code']);
        $params['password'] = $crypt_mdl -> ecb_encrypt($user['password']);
        $params['chk_tag'] = 1;
        $params['app_fmt'] = 'json';
        $params['timestamp'] = $timestamp;
        $params['app_act'] = 'index/login_enter';
        ksort($params);
        $params['sign'] = md5(join('_', $params) . '_' . $secret);
        $ret = $this->curl_req($efast_url,$params);
        if($ret['status']<0){
            return $ret;
        }
        $chk_login_result = json_decode($ret['data'],true);
        if (empty($chk_login_result)){
            return $this->format_ret(-1,'',$ret);
        }
        if ($chk_login_result['status']!=1){
            return $this->format_ret(-1,'',$chk_login_result['message']);
        }
        unset($params['chk_tag']);
        unset($params['sign']);
        ksort($params);
        $params['sign'] = md5(join('_', $params) . '_' . $secret);

        $url = $efast_url . '?' . http_build_query($params);
        //echo '<hr/>url<xmp>'.var_export($url,true).'</xmp>';
        return $this->format_ret(1,$url);
    }

    public function curl_req($url, $params) {
        if (is_array ($params) && 0 < count ($params)) {
            $postBodyString = "";
            foreach ($params as $k => $v) {
                $postBodyString .= "$k=" . urlencode ($v) . "&";
            }
            unset ($k, $v);
            if ($postBodyString) {
                $postBodyString = substr($postBodyString, 0, -1);
            }
        } else {
            $postBodyString = $params;
        }

        $url_get = $url . '?' . $postBodyString;

        $ch = curl_init ();
        curl_setopt ($ch, CURLOPT_URL, $url_get);
        curl_setopt ($ch, CURLOPT_FAILONERROR, false);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_TIMEOUT, 45);

        $logs_arr = array();
        $logs_arr['url'] = $url;
        $logs_arr['param'] = json_encode($param);

        $reponse = curl_exec ($ch);
        if (curl_errno ($ch)) {
            $curl_error = curl_error ($ch);
            return $this -> format_ret(-1, '', $curl_error);
        } else {
            $httpStatusCode = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                return $this -> format_ret(-1, '', 'httpStatusCode=' . $httpStatusCode . ' ' . $reponse);
            }
        }
        curl_close ($ch);
        return $this -> format_ret(1, $reponse);
    }
}
