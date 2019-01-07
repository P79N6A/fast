<?php

require_model('wms/WmsAPIModel');
require_lib('new_xml_util', true);

/**
 * iwmsAPI类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class JdwmsAPIModel extends WmsAPIModel {

    public $api_url;
    private $app_key;
    private $app_secret;
    private $access_token;
    public $method;
    private $version = "3.0";
    private $api_response_conf = array();

    public function __construct($token = array()) {
        if (!empty($token)) {
            $this->set_token($token);
        }
        $this->api_response_conf = array(

            'jingdong.eclp.po.addPoOrder' => 'jingdong_eclp_po_addPoOrder_response',
            'jingdong.eclp.po.cancalPoOrder'=>'jingdong_eclp_po_cancalPoOrder_response',
            'jingdong.eclp.po.queryPoOrder'=>'jingdong_eclp_po_queryPoOrder_response',
            
             'jingdong.eclp.rts.isvRtsTransfer' => 'jingdong_eclp_rts_isvRtsTransfer_response',
            'jingdong.eclp.rts.isvRtsCancel'=>'jingdong_eclp_rts_isvRtsCancel_response',
            'jingdong.eclp.rts.isvRtsQuery'=>'jingdong_eclp_rts_isvRtsQuery_response',
            
            'jingdong.eclp.goods.transportGoodsInfo' => 'jingdong_eclp_goods_transportGoodsInfo_responce',
            'jingdong.eclp.stock.queryStock' => 'jingdong_eclp_stock_queryStock_response',
     
            'jingdong.eclp.order.addOrder' => 'jingdong_eclp_order_addOrder_response',
            'jingdong.eclp.order.queryOrder'=>'jingdong_eclp_order_queryOrder_response',
            'jingdong.eclp.order.cancelOrder'=>'jingdong_eclp_order_cancelOrder_response',
            
            'jingdong.eclp.rtw.transportRtw'=>'jingdong_eclp_rtw_transportRtw_response',
            'jingdong.eclp.rtw.queryRtw' => 'jingdong_eclp_rtw_queryRtw_response',
            
            'jingdong.eclp.order.queryOrderListByStatus' => 'jingdong_eclp_order_queryOrderListByStatus_response',
            
            'jingdong.eclp.goods.transportGoodsInfo' => 'jingdong_eclp_goods_transportGoodsInfo_response',
            
            'jingdong.eclp.order.queryOrderStatus'=> 'jingdong_eclp_order_queryOrderStatus_response',
            
            'jingdong.eclp.goods.queryGoodsInfo'=>'jingdong_eclp_goods_queryGoodsInfo_response',

        );
        // $this->init_method_info();
    }

    function set_token($token) {
        $this->app_key = $token['app_key'];
        $this->app_secret = $token['app_secret'];
        $this->access_token = $token['access_token'];
        $this->api_url = $token['URL'];
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $params = array()) {
        ksort($params);
        if (!empty($params)) {
            $jsonparams = json_encode($params);
        } else {
            $jsonparams = '{}';
        }
        $this->api_name = $api;
        //组装系统参数
        $sysParams ["app_key"] = $this->app_key; //商家app_key
        $sysParams ["access_token"] = $this->access_token; //商家app_key
        $sysParams ["v"] = $this->version; //版本
        $sysParams ["timestamp"] = date("Y-m-d H:i:s"); //时间戳
        $sysParams ["method"] = $api; //方法名
        //获取业务json格式的参数
        $apiParams ["360buy_param_json"] = $jsonparams;
        $sysParams ["sign"] = $this->sign(array_merge($sysParams, $apiParams));
        $requestUrl = $this->buildUrl($sysParams);

        $log_arr['method'] = $api;
        $log_arr['params'] = $apiParams;
        $log_arr['post_data'] = $requestUrl;
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'jdwms';

        //请求接口
        $resp = $this->exec($requestUrl, $apiParams);

        $log_arr['resp'] = $resp;
        load_model('sys/ApiLogsModel')->add_logs($log_arr);
        $this->method = $api;
        return $this->get_response($resp);
    }

    public function get_response($resp) {


        $result = json_decode($resp, true);

        if (empty($result)) {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $resp);
        }

        if (!empty($result['error_response'])) {
            return $this->format_ret(-1, '', '接口返回数据有错:' . $result['error_response']['zh_desc']);
        }

        $ret_status = 1;
        $ret_data = array();
        $msg = '';
        $key = $this->api_response_conf[$this->method];

        if (isset($result[$key])) {
            $ret_data = $result[$key];
        } else {
            $ret_status = -1;
            $msg = "接口返回解析异常：";
        }



        return $this->format_ret($ret_status, $ret_data, $msg);
    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($param = array()) {
        //所有请求参数按照字母先后顺序排序
        ksort($param);
        //定义字符串开始 结尾所包括的字符串
        $stringToBeSigned = $this->app_secret;
        //把所有参数名和参数值串在一起
        foreach ($param as $k => $v) {
            $stringToBeSigned .= "$k$v";
        }
        unset($k, $v);
        //把venderKey夹在字符串的两端
        $stringToBeSigned .= $this->app_secret;
        //使用MD5进行加密，再转化成大写
        return strtoupper(md5($stringToBeSigned));
    }

    /**
     * 组装并生产url
     * @param  $params 系统级参数
     * @return void
     */
    public function buildUrl($params) {
        $requestUrl = $this->api_url . "?";
        foreach ($params as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, - 1);
        return $requestUrl;
    }

}
