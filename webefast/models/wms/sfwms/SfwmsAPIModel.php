<?php

require_model('wms/WmsAPIModel');
require_lib('new_xml_util',true);
/**
 * iwmsAPI类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class SfwmsAPIModel extends WmsAPIModel {

    public $api_url;
    private $checkword;
    public $method;
    public $access_code;
    private $api_config = array(
        #下单
        'ExpressServlet' => array(
            'uri' => '/ExpressServlet',
            'data_title' => 'tporder',
            'require' => array(
                'orderid', 'j_custid', 'j_company', 'j_contact', array('j_tel', 'j_mobile'), 'j_province', 'j_city', 'j_address',
                'd_contact', array('d_tel', 'd_mobile'), 'd_province', 'd_city', 'd_address', 'checkword', 'cargoItems.cargo.0.cargo_name',
            ),
            'type' => 'normal',
        	'req_title' => 'orderResponse',
        ),
        #查询
        'OrderSearchServlet' => array(
            'uri' => '/OrderSearchServlet',
            'data_title' => 'tporder',
            'require' => array(
                'orderid', 'checkword',
            ),
            'type' => 'servlet',
        	'req_title' => 'orderResponse',
        ),
        'logisticQueryStandard' => array(
            'ser' => 'http://service.serviceprovide.module.sf.com/',
            'uri' => '/ws/CustomerService',
            'data_title' => 'mailnoQuery',
            'require' => array(
                'orders', 'checkword'
            ),
            'type' => 'webservice',
        ),
        //wms接口
        'wmsMerchantCatalogService' => array(
            'ser' => 'http://service.warehouse.integration.sf.com/',
            //  'uri' => '/bsp-wms/ws/WarehouseService',
            'data_title' => 'wmsMerchantCatalogRequest',
            'require' => array(
                'company', 'item', 'description', 'storage_template', 'quantity_um_1', 'interface_action_code',
            ),
            'type' => 'webservice',
            'req_title' => 'wmsMerchantCatalogResponse',
        ),
        'wmsPurchaseOrderService' => array(
            'ser' => 'http://service.warehouse.integration.sf.com/',
            // 'uri' => '/bsp-wms/ws/WarehouseService',
            'data_title' => 'wmsPurchaseOrderRequest',
            'require' => array(
                'header.company', 'header.warehouse', 'header.erp_order_num', 'header.erp_order_type', 'header.order_date', 'header.scheduled_receipt_date', 'header.source_id',
                'detailList.item.0.erp_order_line_num', 'detailList.item.0.item', 'detailList.item.0.total_qty',
            ),
            'type' => 'webservice',
            'req_title' => 'wmsPurchaseOrderResponse',
        ),
        'wmsPurchaseOrderQueryService' => array(
            'ser' => 'http://service.warehouse.integration.sf.com/',
            // 'uri' => '/bsp-wms/ws/WarehouseService',
            'data_title' => 'wmsPurchaseOrderQueryRequest',
            'require' => array(
                'company', 'orderid', 'warehouse',
            ),
            'type' => 'webservice',
            'req_title' => 'wmsPurchaseOrderQueryResponse',
        ),
        'wmsSailOrderService' => array(
            'ser' => 'http://service.warehouse.integration.sf.com/',
            // 'uri' => '/bsp-wms/ws/WarehouseService',
            'data_title' => 'wmsSailOrderRequest',
            'require' => array(
                'header.company', 'header.warehouse', 'header.erp_order', 'header.ship_to_name', 'header.order_date', 'header.ship_to_attention_to', 'header.ship_to_address',
                'detailList.item.0.erp_order_line_num', 'detailList.item.0.item', 'detailList.item.0.item', 'detailList.item.0.uom',
            ),
            'type' => 'webservice',
            'req_title' => 'wmsSailOrderResponse',
        ),
        'wmsSailOrderQueryService' => array(
            'ser' => 'http://service.warehouse.integration.sf.com/',
            // 'uri' => '/bsp-wms/ws/WarehouseService',
            'data_title' => 'wmsSailOrderQueryRequest',
            'require' => array(
                'company', 'orderid', 'warehouse',
            ),
            'type' => 'webservice',
            'req_title' => 'wmsSailOrderQueryResponse',
        ),
        'wmsSailOrderStatusQueryService' => array(
            'ser' => 'http://service.warehouse.integration.sf.com/',
            // 'uri' => '/bsp-wms/ws/WarehouseService',
            'data_title' => 'wmsSailOrderStatusQueryRequest',
            'require' => array(
                'company', 'orderid',
            ),
            'type' => 'webservice',
            'req_title' => 'wmsSailOrderStatusQueryResponse',
        ),
        'wmsSailOrderStatusQueryNewService' => array(
            'ser' => 'http://service.warehouse.integration.sf.com/',
            //'uri' => '/bsp-wms/ws/WarehouseService',
            'data_title' => 'wmsSailOrderStatusQueryRequest',
            'require' => array(
                'company', 'orderid',
            ),
            'type' => 'webservice',
            'req_title' => 'wmsSailOrderStatusQueryResponse',
        ),
        'wmsCancelSailOrderService' => array(
            'ser' => 'http://service.warehouse.integration.sf.com/',
            // 'uri' => '/bsp-wms/ws/WarehouseService',
            'data_title' => 'wmsCancelSailOrderRequest',
            'require' => array(
                'company', 'orderid',
            ),
            'type' => 'webservice',
            'req_title' => 'wmsCancelSailOrderResponse',
        ),
        'wmsInventoryBalanceQueryService' => array(
            'ser' => 'http://service.warehouse.integration.sf.com/',
            //'uri' => '/bsp-wms/ws/WarehouseService',
            'data_title' => 'wmsInventoryBalanceQueryRequest',
            'require' => array(
                'company', 'item', 'inventory_sts', 'warehouse',
            ),
            'type' => 'webservice',
            'req_title' => 'wmsInventoryBalanceQueryResponse',
        ),
        'wmsPageQueryInventoryBalanceService' => array(
            'ser' => 'http://service.warehouse.integration.sf.com/',
            //'uri' => '/bsp-wms/ws/WarehouseService',
            'data_title' => 'wmsInventoryBalancePageQueryRequest',
            'require' => array(
                'company', 'warehouse', 'inventory_sts',
            ),
            'type' => 'webservice',
            'req_title' => 'wmsInventoryBalancePageQueryResponse',
        ),
        'PURCHASE_ORDER_SERVICE' => array(
            'service' => 'PURCHASE_ORDER_SERVICE',
            'data_title' => 'PurchaseOrderRequest',
            'type' => 'new_post',
            'req_title' => 'PurchaseOrderResponse',
        ),//入库单下发
        'CANCEL_PURCHASE_ORDER_SERVICE' => array(
            'service' => 'CANCEL_PURCHASE_ORDER_SERVICE',
            'data_title' => 'CancelPurchaseOrderRequest',
            'type' => 'new_post',
            'req_title' => 'CancelPurchaseOrderResponse',
        ),//入库单取消
        'PURCHASE_ORDER_INBOUND_QUERY_SERVICE' => array(
            'service' => 'PURCHASE_ORDER_INBOUND_QUERY_SERVICE',
            'data_title' => 'PurchaseOrderInboundRequest',
            'type' => 'new_post',
            'req_title' => 'PurchaseOrderInboundResponse',
        ),//入库单明细查询
        'SALE_ORDER_SERVICE' => array(
            'service' => 'SALE_ORDER_SERVICE',
            'data_title' => 'SaleOrderRequest',
            'type' => 'new_post',
            'req_title' => 'SaleOrderResponse',
        ),//出库单下发
        'CANCEL_SALE_ORDER_SERVICE' => array(
            'service' => 'CANCEL_SALE_ORDER_SERVICE',
            'data_title' => 'CancelSaleOrderRequest',
            'type' => 'new_post',
            'req_title' => 'CancelSaleOrderResponse',
        ),//出库单取消
        'SALE_ORDER_OUTBOUND_DETAIL_QUERY_SERVICE' => array(
            'service' => 'SALE_ORDER_OUTBOUND_DETAIL_QUERY_SERVICE',
            'data_title' => 'SaleOrderOutboundDetailRequest',
            'type' => 'new_post',
            'req_title' => 'SaleOrderOutboundDetailResponse',
        ),//出库单明细查询
        'SALE_ORDER_STATUS_QUERY_SERVICE' => array(
            'service' => 'SALE_ORDER_STATUS_QUERY_SERVICE',
            'data_title' => 'SaleOrderStatusRequest',
            'type' => 'new_post',
            'req_title' => 'SaleOrderStatusResponse',
        ),//出库单状态查询
        'ITEM_SERVICE' => array(
            'service' => 'ITEM_SERVICE',
            'data_title' => 'ItemRequest',
            'type' => 'new_post',
            'req_title' => 'ItemResponse',
        ),//商品下发接口
        'RT_INVENTORY_QUERY_SERVICE' => array(
            'service' => 'RT_INVENTORY_QUERY_SERVICE',
            'data_title' => 'RTInventoryQueryRequest',
            'type' => 'new_post',
            'req_title' => 'RTInventoryQueryResponse',
        ),//实时库存查询接口
    );

    public function __construct($token) {
        $this->checkword = $token ['checkword'];
        $this->api_url = $token ['URL'];
        //接入编码
        if (!empty($token ['access_code'])){
            $this->access_code = $token ['access_code'];
        }
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $param = array()) {
        $app_url = $this->api_url;
        if (isset($this->api_config[$api]['uri'])) {
            $app_url .= ($this->api_config[$api]['uri']);
        }

        $param['checkword'] = $this->checkword;
        //  list($code, $msg) = $this->check_params($method, $params);

        $header = array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");
        if ($this->api_config[$api]['type'] == 'webservice') {
            $post_data = $this->_getWsPostData($api, $param);
            $header = array(
                "Content-Type: text/xml;charset=UTF-8",
                'SOAPAction: ""',
                'User-Agent: Apache-HttpClient/4.1.1 (java 1.5)',
            );
        } elseif ($this->api_config[$api]['type'] == 'servlet') {
            $post_data = $this->_getServletData($api, $param);
        } elseif ($this->api_config[$api]['type'] == 'normal'){
            $post_data = $this->_getParamArr($api, $param);
        }else {
            //新底层
            $post_data = $this->_getParamArrNew($api, $param);
        }
        $post_data = str_replace('&nbsp;', ' ', $post_data);

        //日志
//        $log_key = $this->get_log_key();
//        $log_arr['mothod'] = $api;
//        $log_arr['param1'] = $param;
//        $log_arr['param'] = $post_data;
//        $this->set_log($log_key, $log_arr, 'sfwms');

        $log_arr['method'] = $api;
        $log_arr['params'] = $param;
        //$this->set_log($log_key, $log_arr, 'ydwms');
        $log_arr['post_data'] = $post_data;
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'sfwms';



        //请求接口
       $resp = $this->exec($app_url, $post_data, 'post', $header);
      /*
        $resp = '<?xml version="1.0" encoding="UTF-8"?>
<orderResponse>
  <orderid>1509210017871</orderid>
  <result>1</result>
  <mailno>590864359090</mailno>
  <originCode>021</originCode>
  <destCode>550</destCode>
</orderResponse>';*/
        //日志
//        $log_arr = array('ret' => $resp);
//        $this->set_log($log_key, $log_arr, 'sfwms');
        $log_arr['resp'] = $resp;
        load_model('sys/ApiLogsModel')->add_logs($log_arr);
        $this->method = $api;
        return $this->get_response($resp);
    }

    public function get_response($resp) {
        $method = &$this->method;
        if ($this->api_config[$method]['type'] == 'webservice') {
            $resp = htmlspecialchars_decode($resp);
            $match = array();
            if (preg_match('{<return>(.*?)</return>}su', $resp, $match)) {
                $resp = $match[1];
            }
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
            } else {
                $ret_data = $result[$this->api_config[$method]['req_title']];
                if ($result['Response']['Head'] == 'PART') {
                    $ret_status = -1;
                }
            }
        } elseif (isset($result[$this->api_config[$method]['req_title']])) {
            $ret_data = $result[$this->api_config[$method]['req_title']];
            if (empty($ret_data['result']) || $ret_data['result'] != 1) {
                $msg = isset($ret_data['remark']) ? $ret_data['remark'] : '';
                if (!empty($ret_data['orderid'])) {
                    $msg = $ret_data['orderid'] . '(' . $msg . ')';
                }
                $ret_status = -1;
            }
        } elseif (isset($result['responseFail'])) {
            $ret_status = -1;
            $ret_data['msg'] = $result['responseFail']['remark'];
        } else {
            $ret_status = -1;
            $ret_data = $result;
            $msg = "解析异常";
        }

        return $this->format_ret($ret_status, $ret_data, $msg);
    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($param = array()) {

        return '';
    }

    protected function _getWsPostData($method, $params) {

        $logistics_interface = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="' . $this->api_config[$method]['ser'] . '">';
        $logistics_interface .= '<soapenv:Header/>';
        $logistics_interface .= '<soapenv:Body>';
        $logistics_interface .= "<ser:$method>";
        $logistics_interface .= "<arg0><![CDATA[";
        $logistics_interface .=  $this->get_data_xml($params,$this->api_config[$method]['data_title']);
        $logistics_interface .= ']]></arg0>';
        $logistics_interface .= "</ser:$method>";
        $logistics_interface .= '</soapenv:Body>';
        $logistics_interface .= '</soapenv:Envelope>';



        //echo $logistics_interface;die;
        return $logistics_interface;
    }

    private function _getParamArr($method, $params) {
        $post_data = array();
        $logistics_interface = "<?xml version='1.0' encoding='utf-8'?><{$this->api_config[$method]['data_title']}>";
        if ($params) {
            $logistics_interface .= $this->get_data_xml($params);
        }
        $logistics_interface .= "</{$this->api_config[$method]['data_title']}>";
        $data_digest = base64_encode(md5(($logistics_interface . $this->checkword), TRUE));
        $data_digest = rawurlencode($data_digest);
        $logistics_interface = rawurlencode($logistics_interface);
        $post_data = "logistics_interface=$logistics_interface&data_digest=$data_digest";
        return $post_data;
    }
    private function _getParamArrNew($method, $params) {
        unset($params['checkword']);
        $req_params = array();
        $data_title = $this->api_config[$method]['data_title'];
        $req_params['Head']['AccessCode'] = $this->access_code;
        $req_params['Head']['Checkword'] = $this->checkword;
        $req_params['Body'][$data_title] = $params;
        $logistics_interface = "<?xml version='1.0' encoding='utf-8'?><Request service='".$this->api_config[$method]['service']."'  lang='zh-CN'>";
        if ($req_params) {
            $logistics_interface .= $this->get_data_xml($req_params);
        }
        $logistics_interface .= "</Request>";
        $data_digest = base64_encode(md5(($logistics_interface . $this->checkword), TRUE));
        $data_digest_en = rawurlencode($data_digest);
        $logistics_interface = rawurlencode($logistics_interface);
        $post_data = "logistics_interface=$logistics_interface&data_digest=$data_digest_en";
        return $post_data;
    }

    private function _getServletData($method, $params) {
        $post_data = "checkword=" . urlencode($params["checkword"]) . "&orderid=" . $params["orderid"];
        return $post_data;
    }

    public function get_data_xml($array, $tag = null) {

       // $xml = $this->array2xml($array, $tag);
    	$xml = array_create_xml($array, $tag);
        $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
        return trim($xml);
    }


}