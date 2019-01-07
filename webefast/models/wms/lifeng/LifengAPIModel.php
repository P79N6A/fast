<?php

require_model('wms/WmsAPIModel');
require_lib('new_xml_util', true);

/**
 * iwmsAPI类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class LifengAPIModel extends WmsAPIModel {

    public $api_url;
    public $method;

    public function __construct($token) {

        $this->api_url = $token ['URL'];
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $param = array()) {
        $app_url = $this->api_url;
        if (isset($this->api_config[$api]['uri'])) {
            $app_url .= ($this->api_config[$api]['uri']);
        }

        $post_data = $this->_getParamArr($api, $param);
        $header = array(
            "Accept-Encoding: gzip,deflate",
            "Content-Type: text/xml;charset=UTF-8",
            'SOAPAction: "sii:LFL_WS_BROOKS_IP_RECEIVE_HTTP"',
            //      'Content-Length: ' . strlen($post_data),
            //   'Host: wcs.lfuat.net:20520',
            'Connection: Keep-Alive',
            'User-Agent: Apache-HttpClient/4.1.1 (java 1.5)',
        );


        $log_arr['method'] = $api;
        $log_arr['params'] = $param;
        //$this->set_log($log_key, $log_arr, 'ydwms');
        $log_arr['post_data'] = $post_data;
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'lifeng';



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
          </orderResponse>'; */
        //日志
  
      //  $this->set_log($log_key, $log_arr, 'sfwms');
        $log_arr['resp'] = $resp;
        load_model('sys/ApiLogsModel')->add_logs($log_arr);
        $this->method = $api;
      //  $resp ='<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://www.w3.org/2003/05/soap-envelope"><SOAP-ENV:Body><s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><GetESBDataPacketsByStreamResponse xmlns="http://www.baison.com"><GetESBDataPacketsByStreamResult>&lt;Response&gt;&lt;BatchID&gt;5016715940&lt;/BatchID&gt;&lt;Result&gt;&lt;ExternDocKey&gt;1708010000100&lt;/ExternDocKey&gt;&lt;Success&gt;true&lt;/Success&gt;&lt;/Result&gt;&lt;/Response&gt;</GetESBDataPacketsByStreamResult></GetESBDataPacketsByStreamResponse></s:Body></s:Envelope></SOAP-ENV:Body></SOAP-ENV:Envelope>';
        return $this->get_response($resp);
    }

    public function get_response($resp) {
        $top = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://www.w3.org/2003/05/soap-envelope"><SOAP-ENV:Body><s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><GetESBDataPacketsByStreamResponse xmlns="http://www.baison.com"><GetESBDataPacketsByStreamResult>';
        $end = '</GetESBDataPacketsByStreamResult></GetESBDataPacketsByStreamResponse></s:Body></s:Envelope></SOAP-ENV:Body></SOAP-ENV:Envelope>';
        $resp = str_replace($top, '', $resp);
        $resp = str_replace($end, '', $resp);
      
        $resp2 = htmlspecialchars_decode($resp);
        $result = $this->xml2array($resp2);
  
        if (isset($result['Response']['Result']['Success'])&&$result['Response']['Result']['Success']=='false') {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $result['Response']['Result']['Description']);
        }
       if (!isset($result['Response']['Result']['Success'])) {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $resp);
        }
        
        return $this->format_ret(1, $result);
    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($param = array()) {

        return '';
    }

    private function _getParamArr($method, $params) {

        $parameters = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:bais="http://www.baison.com">
        <soapenv:Header/>
        <soapenv:Body>
           <bais:GetESBDataPacketsByStream>
              <bais:PacketMarked>{$method}</bais:PacketMarked>
              <bais:Packet><![CDATA[{$xml}]]></bais:Packet>
           </bais:GetESBDataPacketsByStream>
        </soapenv:Body>
     </soapenv:Envelope>'; //logistic_order_notify
        $xml = $this->get_data_xml($params);
        $post_data = str_replace('{$method}', $method, $parameters);
//       $xml = "<WMSORD>  
//            <BatchID>0001289349</BatchID>  <Facility>001</Facility>  <ORDHD>    <PrintFlag>0</PrintFlag>    <ShipperKey>brooks</ShipperKey>    <DeliveryNote/>    <Notes/>    <FinalFlag>1</FinalFlag>    <TotalLines>1</TotalLines>    <CurrReq>1</CurrReq>    <TotalLeft>0</TotalLeft>    <DeliveryPlace/>    <IntermodalVehicle>EXPRESS</IntermodalVehicle>    <Salesman/>    <Type>NORMAL</Type>    <InvoiceAmount>358.00</InvoiceAmount>    <InterfaceActionFlag>A</InterfaceActionFlag>    <Priority>1</Priority>    <Facility>001</Facility>    <StorerKey>brooks</StorerKey>    <OrderDate>2016-10-13 10:09:35</OrderDate>    <M_Company>2427013305426389</M_Company>    <ExternOrderKey>610130000025</ExternOrderKey>    <Consignee>      <C_Zip>000000</C_Zip>      <C_Contact1>??</C_Contact1>      <C_Address1>???</C_Address1>      <Facility>001</Facility>      <C_Address2>??? ??? ??? ??????????????,??????</C_Address2>      <C_State>???</C_State>      <C_City>???</C_City>      <C_Phone1>13866192307</C_Phone1>      <C_Phone2/>    </Consignee>       <ORDDT>      <SKU>155171-30174-XS</SKU>      <Facility>001</Facility>      <OpenQty>1</OpenQty>      <ExternLineNo>1001</ExternLineNo>      <UnitPrice>358.00</UnitPrice>    </ORDDT>  </ORDHD></WMSORD>";
//        
       // $xml = str_replace('><', ">    <", $xml);
        $post_data = str_replace('{$xml}', $xml, $post_data);
        
        return $post_data;
    }

    public function get_data_xml($array, $tag = null) {

        // $xml = $this->array2xml($array, $tag);
        $xml = array_create_xml($array, $tag);
        $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
        return trim($xml);
    }

    
    
    
}
