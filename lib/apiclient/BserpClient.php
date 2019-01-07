<?php

require_lib('apiclient/ApiClient');
require_lib('util/crm_util');

class BserpClient extends ApiClient implements ApiClientInterface {

    //   private $app_url = 'http://vipapis.com'; //正式
    private $app_url = 'http://sandbox.vipapis.com/'; //测试

    /**
     * @var string
     */
    private $appKey = '23359cdf';

    /**
     * @var string
     */
    private $appSecret = '0E6ED2F7113AB39C4FD6C89348A8F07C';

    /**
     * @var string
     */
    private $customerid = '';

    /**
     * 接口类型
     * @var string
     */
    private $serviceType = 0; //业务类型(1 档案 0 单据 )

    /**
     * @var PDODB
     */
    private $db;

    /**
     * url地址参数
     * @var array
     */
    private $url_data;

    /**
     * @param string $api_config
     */
    public function __construct($api_config) {
        $this->appKey = $api_config['key'];
        $this->appSecret = $api_config['api_secret'];
        $this->app_url = $api_config['api_url'];
    }

    function get_goods($param) {
        $param['page'] = isset($param['page']) ? $param['page'] : 1;
        $param['pageSize'] = isset($param['pageSize']) ? $param['pageSize'] : 100;

        $hour = date('H');

        if ($hour > 2 && $hour < 6) {

        } else {
            $param['endDate'] = isset($param['endDate']) ? $param['endDate'] : date('Y-m-d H:i:s');
            $param['startDate'] = isset($param['startDate']) ? $param['startDate'] : date("Y-m-d H:i:s", strtotime($param['endDate']) - 1 * 24 * 60 * 60);
        }

        $this->serviceType = 1;

        $data = $this->getErpData('get.Goods', $param);

        return $data;
    }

    /**
     * 库存查询
     * @param type $param
     * @return type
     */
    function inventory_query($param) {
        $this->serviceType = 1;
        $param['page'] = isset($param['page']) ? $param['page'] : 1;
        $param['pageSize'] = isset($param['pageSize']) ? $param['pageSize'] : 100;

        $data = $this->getErpData('inventory.Query', $param);
        return $data;
    }

    /**
     * 零售单据上传
     * @param type $param
     * @return type
     */
    function retail_confirm($param) {
        $data = $this->getErpData('RetailConfirm', $param);
        return $data;
    }

    /**
     * 零售单据上传
     * @param type $param
     * @return type
     */
    function update_pf_asn($param) {
        $data = $this->getErpData('UpdatePfAsn', $param);
        return $data;
    }

    /**
     * 客户档案获取
     * @param type $param
     * @return type
     */
    function get_customer_list($param) {
        $this->serviceType = 1;
        $data = $this->getErpData('get.CustomerList', $param);
        return $data;
    }

    /**
     * @param $api
     * @param $param
     * @return mixed
     * @throws Exception
     */
    function getErpData($api, $param) {
        //记录请求日志
        $log_arr = [
            'method' => $api,
            'post_data' => $param,
            'url' => $this->app_url,
            'type' => 'bserp2',
        ];
        try {
            $result = $this->exec($api, $param);
            print_r($this->url_data);
            $log_arr['params'] = $this->url_data;
            $log_arr['resp'] = $result;
            load_model('sys/ApiLogsModel')->add_logs($log_arr);
        } catch (Exception $e) {
            $data['status'] = -1;
            $data['message'] = 'ERP接口请求出错:' . $e->getMessage();

            $log_arr['params'] = $this->url_data;
            $log_arr['resp'] = $data['message'];
            load_model('sys/ApiLogsModel')->add_logs($log_arr);
            return $data;
        }
        $data = $this->xml2array($result);

        return $data;
    }

    /**
     * @param $apiName
     * @param $parameters
     * @return array
     */
    public function newHandle($apiName, $parameters) {
        $body = $this->get_data_xml($parameters);
        $arr = $this->createArrayParam($apiName);
        $arr['method'] = $apiName;
        $arr['sign'] = $this->createSign($arr, $body);
        unset($arr['body']);
        
        $this->url_data = $arr;
        
        $handle = array();
        $handle['type'] = "post";
        $handle['headers'] = array("Content-Type:application/xml");
        $handle['url'] = $this->buildUrl($arr);
        $handle['body'] = $body;
        return $handle;
    }

    public function buildUrl($params) {
        $requestUrl = $this->app_url . "?";
        foreach ($params as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . $sysParamValue . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);

        return $requestUrl;
    }

    /**
     * 合并系统参数
     * @param $api
     * @return array
     */
    function createArrayParam($api) {
        $paramArr = array(
            //'key' => $this->appKey,
            // 'format' => 'xml',
            'v' => '1.0',
            'sign_method' => 'md5',
            'requestTime' => date('YmdHis'),
            //     'customerId ' => $this->customerid,
            'method' => $api,
            'serviceType' => $this->serviceType,
        );

        return $paramArr;
    }

    /**
     * 签名函数
     * @param $paramArr
     * @param $body
     * @return string
     */
    function createSign($paramArr, $body) {
        $appSecret = &$this->appSecret;
        $sign = $appSecret;
        ksort($paramArr);

        foreach ($paramArr as $key => $value) {
            if ('' == $key || !isset($value)) {
                continue;
            }
            if ("@" == substr($value, 0, 1)) {
                continue;
            }
            if (is_array($value) && count($value) >= 1) {
                foreach ($value as $_value) {
                    $sign .= $key . $_value;
                }
            } else {
                $sign .= $key . $value;
            }
        }

        $sign .= $body . $appSecret;

        $sign = strtoupper(md5($sign));
        return $sign;
    }

    protected function array2xml($data, $root_tag = NULL, $xmlns = '') {
        require_lib('util/xml_util');
        return array2xml($data, $root_tag, $xmlns);
    }

    public function get_data_xml($array) {
        $xml = $this->array2xml($array, 'request');
        // $xml = str_replace('<?xml version="1.0" encoding="UTF-8">', '', $xml);
        return trim($xml);
    }

    /**
     * xml转数组
     * @param $data
     * @return array
     * @throws Exception
     */
    protected function xml2array($data) {
        require_lib('util/xml_util');
        $return = array();
        xml2array($data, $return);
        return $return;
    }

}
