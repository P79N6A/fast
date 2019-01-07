<?php

require_model('wms/WmsAPIModel');

/**
 * HwwmsAPI类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class HwwmsAPIModel extends WmsAPIModel {

    public $api_url;
    public function __construct($token) {
        $this->api_url = $token ['URL'];
       // $this->Company = $token['Company'];
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($method, $param = array()) {
        $body = $this->get_data_xml($param,$method);
        $length = strlen_utf8($body);
        //Content-length
        $header = array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");
        $return_method = array('GetReceipts','GetShipments');
        $header[] = "Subject:" . $method;
        if (in_array($method, $return_method)){
        	$header[] = "Message-From:TTX";
        	$header[] = "CustomerID:" . $param['CustomerID'];
        	$header[] = "WareHouse:" . $param['WareHouse'];
        	$header[] = "ID:" . $param['ID'];
        	$body = '';
        } else {
        	$header[] = "Message-From:baison";
        }
        //请求接口
        $resp = $this->exec($this->api_url, $body, 'post', $header);
        //日志
        //$log_key = $this->get_log_key();
        
        $log_arr['method'] = $method;
        $log_arr['params'] = $header;
        $log_arr['post_data'] = $param;
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'hwwms';
        $log_arr['resp'] = $resp;
        load_model('sys/ApiLogsModel')->add_logs($log_arr);
        //日志
//        $log_arr = array('ret' => $resp);
//        $this->set_log($log_key, $log_arr, 'hwwms');
        if (in_array($method, $return_method)){
        	return $this->format_ret(1,$this->xml2array($resp));
        }

        return $this->get_response($resp,$method,$return_method);
    }

    public function get_response($resp) {
    	$result =  $this->xml2array($resp);
    	if ($result['Response']['Success'] != 'true'){
    		return $this->format_ret(-1,$result);
    	} else {
    		return $this->format_ret(1,$result);
    	}
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
    
    public function get_data_xml($array,$method) {
    	$xml =  $this->array2xml($array, $method);
    	$xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
    	return trim($xml);
    }
}
