<?php
set_time_limit(0);
require_lib('apiclient/ApiClient');
class SapApiModel extends ApiClient implements ApiClientInterface {

    protected $api_url = '';
    protected $ASHOST, $SYSNR, $CLIENT, $USER, $PASSWD;

    function __construct($api_param) {
        parent::__construct();
        $this->ASHOST = $api_param['ASHOST'];
        $this->SYSNR = $api_param['SYSNR'];
        $this->LIENT = isset($api_param['LIENT'])?$api_param['LIENT']:'';
        $this->USER = isset($api_param['USER'])?$api_param['USER']:'';
        $this->PASSWD = isset($api_param['PASSWD'])?$api_param['PASSWD']:'';
    }

    function request_api($apiName, $api_params) {
        
        $params['param'] = json_encode($api_params);
        $params['timestamp'] = date('Y-m-d H:i:s');
        //   $params['timestamp'] = '2016-10-08 16:40:14';
        //请求接口
       
        $response = $this->exec($apiName, $params);

        return $this->jsonDecode($response);
    }

    public function newHandle($apiName = '', $parameters = array()) {


        $parameters['api_name'] = $apiName;
        $parameters['timestamp'] = date('Y-m-d H:i:s');
        $parameters['sign'] = $this->sign($parameters);

    

        $handle = array();
        $handle['type'] = "post";
        $handle['url'] = $this->ASHOST;

        $handle['body'] = $parameters;
        return $handle;
    }

    public function sign($parameters) {
        $sign = $this->SYSNR . $parameters['api_name'] . $parameters['timestamp'] . $this->SYSNR;
   
        return strtoupper(md5(md5($sign)));
    }

}
