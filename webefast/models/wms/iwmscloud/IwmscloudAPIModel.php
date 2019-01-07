<?php

require_model('wms/WmsAPIModel');

/**
 * iwmsAPI类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class IwmscloudAPIModel extends WmsAPIModel {

    public $api_url;
    private $accesskey;
    private $app_secret;

    public function __construct($token) {
//            		'accesskey' => $api['accesskey'],
////			'app_secret' => $api['secretcode'],
////			'api_url' => $data['wms_address']

//        $this->app_secret = $token ['secretcode'];
//        $this->api_url = $token ['URL'];
//        $this->accesskey = $token ['accesskey'];
        $this->set_token($token);
    }

    function set_token($token) {
        $this->app_secret = $token ['secretcode'];
        $this->api_url = $token ['URL'];
        $this->accesskey = $token ['accesskey'];
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $param = array()) {

        $data = array();
        $data['accesskey'] = $this->accesskey;
        $data['service'] = $api;
        $data['timestamp'] = date('Y-m-d H:i:s');
        $data['v'] = '1.0';
        $data['service_ver'] = '1.0';
        $data = array_merge($data, array('bizdata' => json_encode($param)));
        $sign = $this->sign($data);
        $data['sign'] = $sign;

        //日志
//        $log_key = $this->get_log_key();
//        $log_arr['mothod'] = $api;
//        $log_arr['param'] = $data;
//        $this->set_log($log_key, $log_arr, 'iwms');
        $log_arr['method'] = $api;
        $log_arr['params'] = $data;
        $log_arr['post_data'] = $param;
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'iwmscloud';

        //请求接口
        $resp = $this->exec($this->api_url, $data);

        //日志
//        $log_arr = array('ret' => $resp);
//        $this->set_log($log_key, $log_arr, 'iwms');
        $log_arr['resp'] = $resp;
        load_model('sys/ApiLogsModel')->add_logs($log_arr);


        return $this->get_response($resp);
    }

    public function get_response($resp) {

        $pos = strpos($resp, '{');
        if ($pos > 0) {
            $resp = substr($resp, $pos);
        }
        $result = json_decode($resp, true);
        if (empty($result)) {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $resp);
        }
        if (isset($result['resp_error']['app_err_msg'])) {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $result['resp_error']['app_err_msg']);
        }
        if ($result['flag'] <> 'ACK') {
            return $this->format_ret(-1, $result['data'], '接口返回数据有错.' . $result['data']['msg']);
        }
        return $this->format_ret(1, $result['data']);
    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($param = array()) {
        $sign = $this->app_secret;

        ksort($param);

        foreach ($param as $key => $val) {
            $sign .= $key . $val;
        }

        $sign .= $this->app_secret;

        $sign = md5($sign);

        return $sign;
    }

}
