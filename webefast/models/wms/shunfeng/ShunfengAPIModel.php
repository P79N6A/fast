<?php

require_model('wms/WmsAPIModel');
require_lib('new_xml_util', true);

/**
 * iwmsAPI类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class ShunfengAPIModel extends WmsAPIModel {

    public $api_url;
    private $checkword;
    public $method;
    public $access_code;
    private $api_config = array(

        'CANCEL_SALE_ORDER_SERVICE' => array(
            'service' => 'CANCEL_SALE_ORDER_SERVICE',
            'data_title' => 'CancelSaleOrderRequest',
            'req_title' => 'CancelSaleOrderResponse',
        ),//出库单取消
        'ITEM_SERVICE' => array(
            'service' => 'ITEM_SERVICE',
            'data_title' => 'ItemRequest',
            'req_title' => 'ItemResponse',
        ),//商品下发接口
        'SALE_ORDER_SERVICE' => array(
            'service' => 'SALE_ORDER_SERVICE',
            'data_title' => 'SaleOrderRequest',
            'req_title' => 'SaleOrderResponse',
        ), //出库单下发
        'SALE_ORDER_OUTBOUND_DETAIL_QUERY_SERVICE' => array(
            'service' => 'SALE_ORDER_OUTBOUND_DETAIL_QUERY_SERVICE',
            'data_title' => 'SaleOrderOutboundDetailRequest',
            'req_title' => 'SaleOrderOutboundDetailResponse',
        ),//出库单明细查询
    
//        'CANCEL_SALE_ORDER_SERVICE' => array(
//            'service' => 'CANCEL_SALE_ORDER_SERVICE',
//            'data_title' => 'CancelSaleOrderRequest',
//            'type' => 'new_post',
//            'req_title' => 'CancelSaleOrderResponse',
//        ),

//        'SALE_ORDER_STATUS_QUERY_SERVICE' => array(
//            'service' => 'SALE_ORDER_STATUS_QUERY_SERVICE',
//            'data_title' => 'SaleOrderStatusRequest',
//            'type' => 'new_post',
//            'req_title' => 'SaleOrderStatusResponse',
//        ),//出库单状态查询
        'CANCEL_PURCHASE_ORDER_SERVICE' => array(
            'service' => 'CANCEL_PURCHASE_ORDER_SERVICE',
            'data_title' => 'CancelPurchaseOrderRequest',
            'req_title' => 'CancelPurchaseOrderResponse',
        ),//入库单取消

        'RT_INVENTORY_QUERY_SERVICE' => array(
            'service' => 'RT_INVENTORY_QUERY_SERVICE',
            'data_title' => 'RTInventoryQueryRequest',
            'req_title' => 'RTInventoryQueryResponse',
        ),//实时库存查询接口
        'PURCHASE_ORDER_SERVICE' => array(
            'service' => 'PURCHASE_ORDER_SERVICE',
            'data_title' => 'PurchaseOrderRequest',
            'req_title' => 'PurchaseOrderResponse',
        ),//入库单接口
        'PURCHASE_ORDER_INBOUND_QUERY_SERVICE' => array(
            'service' => 'PURCHASE_ORDER_INBOUND_QUERY_SERVICE',
            'data_title' => 'PurchaseOrderInboundRequest',
            'req_title' => 'PurchaseOrderInboundResponse',
        ),//入库单mingxi
    );

    public function __construct($token) {
        $this->checkword = $token ['checkword'];
        $this->api_url = $token ['URL'];
        //接入编码
        if (!empty($token ['AccessCode'])) {
            $this->access_code = $token ['AccessCode'];
        }
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $param = array()) {
        $app_url = $this->api_url;
        $param['checkword'] = $this->checkword;
        $header = array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");
        $req_params = array();
        $post_data = $this->_getParamArrNew($api, $param,$req_params);
        $log_arr['method'] = $api;
        $log_arr['params'] = $req_params;
        $log_arr['post_data'] = $post_data;
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'shunfengwms';
        
        //请求接口
        $resp = $this->exec($app_url, $post_data, 'post', $header);
        usleep(1000);
        $log_arr['resp'] = $resp;
        load_model('sys/ApiLogsModel')->add_logs($log_arr);
        $this->method = $api;
        return $this->get_response($resp);
    }

    public function get_response($resp) {
        $method = &$this->method;
        $resp = htmlspecialchars_decode($resp);
        $match = array();
        if (preg_match('{<return>(.*?)</return>}su', $resp, $match)) {
            $resp = $match[1];
        }
 
        $result = $this->xml2array($resp);
        if (empty($result)) {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $resp);
        }
        $ret_status = 1;
        $ret_data = array();
        $msg = '';
        if (isset($result['Response']['Head'])) {
            if ($result['Response']['Head'] == 'ERR') {
                $msg = $result['Response']['Error'];
                $ret_status = -1;
            } else {
                $ret_data = $result['Response']['Body'][$this->api_config[$method]['req_title']];
                if($result['Response']['Head'] == 'PART'){
                    $ret_status = 2;
                }
            }
        } else {
            $ret_status = -1;
            $ret_data = $result;
            $msg = "解析异常";
        }

        return $this->format_ret($ret_status, $ret_data, $msg);
    }

    private function _getParamArrNew($method, $params,&$req_params) {
        unset($params['checkword']);
        $data_title = $this->api_config[$method]['data_title'];
        $req_params['Head']['AccessCode'] = $this->access_code;
        $req_params['Head']['Checkword'] = $this->checkword;
        $req_params['Body'][$data_title] = $params;
    
        $logistics_interface .= '<Request service="'.$this->api_config[$method]['service'].'" lang="zh-CN">';
        if ($req_params) {
            $logistics_interface .= $this->get_data_xml($req_params);
        }
        $logistics_interface .= '</Request>';
        $data_digest = base64_encode(md5(($logistics_interface . $this->checkword), TRUE));
        $data_digest_en = rawurlencode($data_digest);
        $logistics_interface = rawurlencode($logistics_interface);
        $post_data = "logistics_interface=$logistics_interface&data_digest=$data_digest_en";
        return $post_data;
    }

    public function get_data_xml($array, $tag = null) {

        $xml = array_create_xml($array, $tag);
        $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
        return trim($xml);
    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($param = array()) {

        return '';
    }

}
