<?php

require_model('wms/WmsAPIModel');

/**
 * BswmsAPI类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class BswmsAPIModel extends WmsAPIModel {

    public $api_url;
    private $partnerId;
    private $partnerKey;

    public function __construct($token) {
        $this->partnerId = $token['partnerId'];
        $this->partnerKey = $token['partnerKey'];
        $this->api_url = $token['URL'];
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($method, $param = array()) {
        //3.0
        $data = array();
        $data['bizData'] = $this->get_data_xml($param, $method);
        $data['msgType'] = 'sync';
        $data['serviceType'] = $method;
        $data['msgId'] = $this->create_guid();
        $data['notifyUrl'] = '';
        $data['serviceVersion'] = '1.0';
        $data['partnerId'] = $this->partnerId;
        $data['partnerKey'] = $this->partnerKey;
        $data['sign'] = $this->sign($data);
        $this->method = $method;
        //日志v
//        $log_key = $this->get_log_key();
//        $log_arr['mothod'] = $method;
//        $log_arr['param'] = $data;
//        $this->set_log($log_key, $log_arr, 'bswms');
        //请求接口
//        var_dump($data);exit;
        unset($data['partnerKey']);       
        $resp = $this->exec($this->api_url, $data);
        $log_arr['method'] = $method;
        $log_arr['params'] = $data;
        $log_arr['post_data'] = $param;
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'bswms';
        $log_arr['resp'] = $resp;
        load_model('sys/ApiLogsModel')->add_logs($log_arr);
        //日志
//        $log_arr = array('ret' => $resp);
//        $this->set_log($log_key, $log_arr, 'bswms');
        $result = $this->get_response($resp, $method);
        return $result;
    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($param = array()) {
        $sign = '';
        $keyArr = explode(',', 'partnerId,bizData,partnerKey,msgId,msgType,serviceType,serviceVersion,notifyUrl');
        $tArr = array();
        foreach ($keyArr as $keyName) {
            $tArr[] = $keyName . '=' . $param[$keyName];
        }
        $sign = md5(join('&', $tArr));
        return $sign;
    }

    function create_guid() {
        $uuid = strtoupper(md5(uniqid(mt_rand(), true)));
        return $uuid;
    }

    public function get_data_xml($array, $method) {
        $xml = $this->array2xml($array, $method);
        return trim($xml);
    }

    public function get_response($resp) {
        $result = $this->xml2array($resp);
        if ($result['SysProtocol']['response']['flag'] != 'SUCCESS') {
            return $this->format_ret(-1, $result);
        } else {
            $bizData = $this->xml2array(urldecode($result['SysProtocol']['response']['bizData']));
            return $this->format_ret(1, $bizData);
//            if ($this->method == 'SyncSalesOrderInfo') {
//                if ($bizData['SalesOrderInfo']['flag'] == 'FAILURE') {
//                    return $this->format_ret(-2, $bizData['SalesOrderInfo']['errors']);
//                }
//            }
//
//            if ($this->method == 'SyncRmaInfo') {
//                if ($bizData['RmaInfo']['flag'] == 'FAILURE') {
//                    return $this->format_ret(-2, $bizData['RmaInfo']['errors']);
//                }
//            }
//
//            if ($this->method == 'SyncProductInfo') {
//                if ($bizData['ProductInfo']['flag'] == 'FAILURE') {
//                    return $this->format_ret(-2, $bizData['ProductInfo']['errors']);
//                }
//            }
//            return $this->format_ret(1, $bizData);
        }
    }

}
