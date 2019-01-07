<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HlhdjModel
 *
 * @author wq
 */
require_lib('apiclient/ApiClient');

class HlhdjModel extends ApiClient implements ApiClientInterface {

    // var $api_url = 'https://test.hlhdj.cn/console/';
    var $api_url = 'https://console.hlhdj.cn/';
    var $secret = '';

    function request_api($apiName, $params) {

        if (empty($this->secret)) {
            $ret_info = $this->get_auth_info();
            if ($ret_info['status'] < 0) {
                return $ret_info;
            }
            $this->secret = $ret_info['data']['secret'];
        }
        $response = $this->exec($apiName, $params);
     
        return $this->get_response($response);
    }

    function get_response($response) {
        $return = json_decode($response, true);
        if (empty($return)) {
            return $this->format_ret(-1, '接口异常:' . $response);
        }

        if (isset($return['result']) && $return['result'] == 'ok') {
            return $this->format_ret(1, $return);
        }
        if (isset($return['result']) && $return['result'] == 'fail') {
            return $this->format_ret(-1, $return, '接口返回错误：' . $return['errorMsg']);
        }
        return $this->format_ret(-1, '接口异常:' . $response);
    }

    function send_order($data) {

        $params = array(
            'orderNo' => $data['tid'],
            'type' => 10,
            'expressCode' => $data['company_code'],
            'expressNumber' => $data['express_no'],
        );
        $params['detail'] = $this->get_detail_sku($data['sell_record_code']);
        $params['expressName'] = $this->get_express_name($data['company_code']);
        $apiName = 'api/order/erp/notify';
        return $this->request_api($apiName, $params);
    }

    function get_detail_sku($sell_record_code) {
        $data = CTX()->db->get_all("select s.barcode as sku from oms_sell_record_detail d
                        INNER JOIN goods_sku s on d.sku=s.sku
                         where d.sell_record_code=:sell_record_code", array(':sell_record_code' => $sell_record_code));
        return $data;
    }

    function get_express_name($express_code) {
        static $express_data = null;
        if (empty($express_data)) {
            $data = CTX()->db->get_all("select company_code,company_name from base_express_company");
            foreach ($data as $val) {
                $express_data[$val['company_code']] = $val['company_name'];
            }
        }
        return $express_data[$express_code];
    }

    public function newHandle($apiName = '', $parameters = array()) {

        $arr['sign'] = $this->sign($parameters);
        $handle = array();
        $handle['type'] = "post";
        $handle['url'] = $this->api_url . $apiName;
      
        $arr = array_merge($arr, $parameters);
        $handle['body'] = json_encode($arr);
        $handle['headers'] = array("Content-Type:application/json;charset=UTF-8");


        return $handle;
    }

    function get_auth_info() {
        static $data = null;
        if (empty($data)) {
          $params['kh_id'] = CTX()->saas->get_saas_key();
         $data = load_model('sys/sysServerModel')->osp_server('get.kh.apiauth',$params);
        }
    //    $data['secret'] = '124312312312';
        return $data;
    }

    function sign($param) {
        // global $secret;
        //   $secret = 'bd34eb1bc8027dfd50f46ce535d0b583';
        $sign = $this->secret;

        ksort($param);
        foreach ($param as $k => $v) {
            if (is_array($v)) {
                $sign .= "$k".json_encode($v);
            }else{
                $sign .= "$k$v";
            }
        }
        unset($k, $v);
        $sign .= $this->secret;

        // echo $sign;die;
        return strtoupper(md5($sign));
    }

}
