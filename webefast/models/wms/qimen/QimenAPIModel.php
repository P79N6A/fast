<?php

require_model('wms/WmsAPIModel');

/**
 * QimenAPIModel类
 * @author wq
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class QimenAPIModel extends WmsAPIModel {

    public $api_url;
    private $app_key;
    private $app_secret;
    private $app_session;
    private $customerid;
    public $url_data;

    public function __construct($token) {
        if (!empty($token)) {
            $this->api_url = isset($token ['URL']) ? $token ['URL'] : '';
            $this->app_key = $token ['app_key'];
            $this->app_secret = $token ['app_secret'];
            $this->app_session = $token ['session'];
            $this->customerid = $token ['customerid'];
        }
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $param = array()) {

        $this->url_data = array(
            'app_key' => $this->app_key,
            //  'session' => $this->app_session,
            'format' => 'xml',
            'v' => '2.0',
            'sign_method' => 'md5',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $api,
            'customerId' => $this->customerid
        );

        $body = $this->get_data_xml($param);

        $sign = $this->sign($body);
        $this->url_data['sign'] = $sign;

        $url = $this->get_url();
        //日志
//        $log_key = $this->get_log_key();
//        $log_arr['mothod'] = $api;
//        $log_arr['url_data'] = $this->url_data;
//        $log_arr['param'] = $body;
//        $this->set_log($log_key, $log_arr, 'qimei');

        $log_arr['method'] = $api;
        $log_arr['params'] = $this->url_data;
        $log_arr['post_data'] = $param;
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'qimen';

        //    $header = array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");
        $header = array("Content-Type:application/xml;charset=UTF-8");

        //请求接口
        $resp = $this->exec($url, $body, 'post', $header);

        //日志
//        $log_arr = array('ret' => $resp);
//        $this->set_log($log_key, $log_arr, 'qimei');
        $log_arr['resp'] = $resp;
        load_model('sys/ApiLogsModel')->add_logs($log_arr);

        return $this->get_response($resp);
    }

    public function get_response($resp) {
        $result = $this->xml2array($resp);
        if (empty($result)) {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $resp);
        }
        if (isset($result['response']['flag']) && $result['response']['flag'] == 'failure') {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $result['response']['message']);
        }
        if (empty($result['response'])) {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $resp);
        }
        return $this->format_ret(1, $result['response']);
    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($param) {
        $sign = $this->app_secret;
        $arr = $this->url_data;
        ksort($arr);

        foreach ($arr as $key => $val) {
            $sign .= $key . $val;
        }

        $sign .=$param . $this->app_secret;

        $sign = strtoupper(md5($sign));

        return $sign;
    }

    public function get_url() {
        $urlString = "";
        foreach ($this->url_data as $k => $v) {
            $urlString .= "$k=" . urlencode($v) . "&";
        }
        return $this->api_url . "?" . rtrim($urlString, '&');
    }

    public function get_data_xml($array) {
        $xml = $this->array2xml($array, 'request');
        $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
        return '<?xml version="1.0" encoding="UTF-8"?>' . trim($xml);
    }

    function set_token($token) {
        $this->api_url = $token ['URL'];
        $this->app_key = $token ['app_key'];
        $this->app_secret = $token ['app_secret'];
        //$this->app_session = $token ['session'];
        $this->customerid = $token ['customerid'];
    }

}
