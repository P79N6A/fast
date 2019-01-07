<?php

require_model('wms/WmsAPIModel');

/**
 * iwmsAPI类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class YdwmsAPIModel extends WmsAPIModel {

    public $api_url;
    private $appkey;
    private $appSecret;
    private $apptoken;
    private $customerid;
    private $warehouseid;
    private $method_arr;

    public function __construct($token = array()) {
        if (!empty($token)) {
            $this->set_token($token);
        }
        $this->init_method_info();
    }

    function set_token($token) {
        $this->appkey = $token['appkey'];
        $this->appSecret = $token['appSecret'];
        $this->apptoken = $token['apptoken'];
        $this->api_url = $token['URL'];
        $this->customerid = $token['customerid'];
        $this->warehouseid = $token['warehouseid']; //     outside_code
//                $this->appkey = 'test';
//                $this->appSecret = '12345678';
//                $this->apptoken = '810AC1A3F-F949-492C-A024-7044B28C8025';
//		//$this->api_url = 'http://dev.yundasys.com:15105/oms/api.php';
//                $this->api_url = 'http://localhost/efast/webefast/web/ydwmsapi.php';
//                $this->customerid = 'EFAST001';
//                $this->warehouseid = 'WH01'; 
    }

    function init_method_info() {
        $this->method_arr = array(
            'putSKUData' => array('messageid' => 'SKU', 'type' => 0),
            'putCustData' => array('messageid' => 'CUSTOMER', 'type' => 0),
            'putASNData' => array('messageid' => 'ASN', 'type' => 0),
            'confirmASNData' => array('messageid' => 'ASNCF', 'type' => 0),
            'cancelASNData' => array('messageid' => 'ASNC', 'type' => 0),
            'putSOData' => array('messageid' => 'SO', 'type' => 0),
            'cancelSOData' => array('messageid' => 'SOC', 'type' => 0),
            'queryINVData' => array('messageid' => 'INVQ', 'type' => 0),
            'queryOrderProcess' => array('messageid' => 'PRCSQ', 'type' => 0),
        );
    }

    public function request_send2($api, $param) {
        $data = array();
        $data['appkey'] = $this->appkey;
        $data['apptoken'] = $this->apptoken;
        $data['customerid'] = $this->customerid;
        $data['warehouseid'] = $this->warehouseid;
        $data['method'] = $api;
        $data['messageid'] = $this->method_arr[$api]['messageid'];
        $data['timestamp'] = date('Y-m-d H:i:s');

        $data['data'] = $param;


        $sign = $this->sign($data['data']);
        $data['sign'] = $sign;
        $log_arr['method'] = $api;
        $log_arr['params'] = '';
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'ydwms';
        $log_arr['post_data'] = $data;
        $resp = $this->exec($this->api_url, $data, 'post', array(), array('timeout' => 120));
        
        $log_arr['resp'] = $resp;
        load_model('sys/ApiLogsModel')->add_logs($log_arr);


        $result = $this->get_response($resp);

        if ($result['data']['return']['returnCode'] != '0000') {

            //   var_dump($result,$data,$resp);die;
        }
        return $result;
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $param = array()) {

        $data = array();
        $data['appkey'] = $this->appkey;
        $data['apptoken'] = $this->apptoken;
        $data['customerid'] = $this->customerid;
        $data['warehouseid'] = $this->warehouseid;
        $data['method'] = $api;
        $data['messageid'] = $this->method_arr[$api]['messageid'];
        $data['timestamp'] = date('Y-m-d H:i:s');

        $data['data'] = $this->get_data_xml($param);


        $sign = $this->sign($data['data']);
        $data['sign'] = $sign;

        //日志
//        $log_key = $this->get_log_key();
        $log_arr['method'] = $api;
        $log_arr['params'] = $data;
        //$this->set_log($log_key, $log_arr, 'ydwms');
        $log_arr['post_data'] = $param;
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'ydwms';
        
        $resp = $this->exec($this->api_url, $data);
        //日志
        //$log_arr = array('ret' => $resp);
        //$this->set_log($log_key, $log_arr, 'ydwms');
        $log_arr['resp'] = $resp;
        load_model('sys/ApiLogsModel')->add_logs($log_arr);

        $result = $this->get_response($resp);

        // if($result['data']['return']['returnCode']!='0000'){
        //   var_dump($result,$data,$resp);die;
        // }
        return $result;
    }

    public function get_response($resp) {
        $result = $this->xml2array($resp);
        if (!isset($result['Response'])) {
            return $this->format_ret(-1, $resp);
        } else {
            return $this->format_ret(1, $result['Response']);
        }
    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($data_xml) {

        $sign = $this->appSecret . $data_xml . $this->appSecret;
        // var_dump($sign);
        $sign = md5($sign);
        //var_dump($sign);die;
        $sign = strtoupper(base64_encode($sign));
        //  $sign = urlencode($sign);
        return $sign;
    }

    public function get_data_xml($array) {

        //   $xml =  $this->array2xml($array, 'xmldata');
        //$xml = "<![CDATA[".$xml."]]>";
        require_lib('new_xml_util');
        $xml = array_create_xml($array, 'xmldata');
        $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
        return trim($xml);
    }

}
