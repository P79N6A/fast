<?php
require_lib('net/HttpClient');
require_model('tb/TbModel');
class BserpApiModel extends TbModel {
    public $method_config = array(
        'o2o_sell_record_upload'=>array(
            'method'=>13,
            'app_mode'=>'WXDD_DoBusinessProcess',
            
        ),
        'o2o_return_record_upload'=>array(
            'method'=>13,
            'app_mode'=>'WXTD_DoBusinessProcess',
            
        ),
        'o2o_trade_status'=>array(
            'method'=>1017,
            'app_mode'=>'EfastBusiness_DoBusinessProcess',
            
        ),
        'o2o_trade_cancel'=>array(
            'method'=>711,
            'app_mode'=>'EfastBusiness_DoBusinessProcess',
            
        ),
    );
    private $api_url = '';
    private $app_key = '';
    public function __construct($token) {
        parent::__construct();
        //print_r($token);die;
        $this->app_key = $token ['erp_key'];
        $this->api_url = $token ['erp_address'];
    }
    public function request_send($method, $params){
        $method_config = $this->method_config[$method];
        
        $params['Key'] = $this->app_key;
        $params['OP'] = $method_config['method'];
        $params['app_mode'] = $method_config['app_mode'];
        //日志
        $log_arr['method'] = $method;
        $log_arr['params'] = $params;
        $log_arr['post_data'] = $params;
        $log_arr['url'] = $this->api_url;
        $log_arr['type'] = 'o2o_erp';
        //请求接口
       // echo $this->api_url.'==';
       // print_r($params);die;
       $resp = $this->exec($this->api_url, $params, 'post');
        $log_arr['resp'] = $resp;
        load_model('sys/ApiLogsModel')->add_logs($log_arr);
        $this->method = $method;
        return $this->get_response($resp);
        
    }
    
    /**
     * 执行单个API请求
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param string $url 请求的URL地址
     * @param array $parameters 请求参数
     * @param string $type post or get
     * @param array $headers header信息
     * @return mixed
     * @throws Exception
     * @see lib/apiclient/ApiClient::exec
     * @depends lib/net/HttpClient
     */
    public function exec($url, $parameters, $type = 'post', $headers = array(),$other = array()) {
        $h = new HttpClient();
        $h->newHandle('0', $type, $url, $headers, $parameters,$other);
        $h->exec();

        $result = $h->responses();
        if (!isset($result['0'])) {
            throw new Exception('请求出错, 返回结果错误');
        }

        return $result['0'];
    }
    function get_response($resp){
        $result = json_decode($resp, true);
         if(empty($result))
        {

            return $this->format_ret(-1, '','bserp 接口连接超时或数据解析错误.');
        }

        if((isset($result['msg']) && $result['msg'] == "success") )
        {
            return $this->format_ret(1, $result);
        }
        if  (isset($result['code']) && $result['code']>=0){
            return $this->format_ret(1, $result);
        }
        else 
        {
            if (!isset($result['Code'])) {
                    $result['Code'] = -2;
            }
            
            return $this->format_ret($result['Code'], $result, $result['ret']);

        }
    }
   
}

