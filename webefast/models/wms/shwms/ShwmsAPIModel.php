<?php

require_model('wms/WmsAPIModel');

/**
 * shwmsAPI类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class ShwmsAPIModel extends WmsAPIModel {

    public $api_url;
    private $username;
    private $passwd;


    private $method;

    public function __construct($token = array()) {
        if (!empty($token)) {
            $this->set_token($token);
        }

    }

    function set_token($token) {
        $this->username = $token['username'];
        $this->passwd = $token['passwd'];

        $this->api_url = $token['URL'];

    }



    public function request_send2($api, $param) {
        $data = array();
        $data['appkey'] = $this->appkey;
        $data['apptoken'] = $this->apptoken;
        $data['customerid'] = $this->customerid;
        $data['warehouseid'] = $this->warehouseid;
        $data['method'] = $api;
        $this->method = $api;
        $data['messageid'] = $this->method_arr[$api]['messageid'];
        $data['timestamp'] = date('Y-m-d H:i:s');

        $data['data'] = $param;


        $sign = $this->sign($data['data']);
        $data['sign'] = $sign;
        $log_arr['method'] = $api;
        $log_arr['params'] = '';
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'shwms';
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
        $data['username'] = $this->username;
        $data['passwd'] = $this->passwd;
        $data['method'] = $api;
        $data = array_merge($data,$param);
        $this->method = $api;
        //日志
//        $log_key = $this->get_log_key();
        $log_arr['method'] = $api;
        $log_arr['params'] = $data;
        //$this->set_log($log_key, $log_arr, 'ydwms');
        $log_arr['post_data'] = $param;
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'ydwms';
        $header = array("Content-Type: application/json; charset=UTF-8");
        $body = json_encode($data);
        //test
       // $body = '{"username":"ok","passwd":"python","method":"erp.trade.add","order":{"ref":"1847951466255879-7","channel_code":"ec","trade_pattern":"b1","date_order":"2016-07-01 10:20:46","buyer":"hxy@qq.com","seller":"18623651929","receive_info":"黄学友|500384198602200316|15923373573|中国|重庆|重庆市|南岸区|重庆市重庆市南岸区南坪亚太商谷4栋2-23","total_import_taxes":4.64,"amount_untaxed":29.0,"amount_total":43.64,"delivery_fee":10.0,"buyer_reg_no":"15923373573","buyer_name":"黄学友","buyer_id_type":"1","buyer_id_card":"500384198602200316","order_line":[{"sku":"P0051592","name":"(保税)新西兰 贺寿利 儿童补钙奶饼干奶油","price":29.0,"num":1,"tax_fee":4.64,"sub_total":33.64,"sub_total_untaxed":29.00,"b2b_lots":"1111-none,2222-b,333-a",}],"payment_line":[{"payment_account":"weixi","payamount":43.64,"payment_no":"4003192001201606187496473787"}]}}';
      //  $body = '{"username":"ok","passwd":"python","method":"erp.order.state.get","order":"2016071810547741"}';
        $resp = $this->exec($this->api_url, $body, 'post', $header);
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
      
        $response_key = str_replace('.', '_',  $this->method)."_response";  

	$pattern = '/(\s+)/i';
	$resp = preg_replace($pattern, '', $resp);
        $result = json_decode($resp, true);

        if (isset($result[$response_key]['data'])) {
            return $this->format_ret(1, $result[$response_key]);
        } else {
            $msg = isset($result[$response_key]['error_response']['msg'])?$result[$response_key]['error_response']['msg']:$resp;

            return $this->format_ret(-1, '',$msg);
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



}
