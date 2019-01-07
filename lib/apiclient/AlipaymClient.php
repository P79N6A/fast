<?php

class AlipaymClient {

    protected $partner;
    protected $app_key;
    protected $return_url = '';
    protected $notify_url = '';
    protected $error_notify_url = '';
    var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    var $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';

    public function __construct($pid, $app_key) {
        $this->partner = $pid;
        $this->app_key = $app_key;
    }

    public function set_url($return_url = '', $notify_url = '', $error_notify_url = '') {
        if (isset($return_url)) {
            $this->return_url = $return_url;
        }
        if (isset($notify_url)) {
            $this->notify_url = $notify_url;
        }
        if (isset($error_notify_url)) {
            $this->error_notify_url = $error_notify_url;
        }
    }

    function check_notify_data($request) {
        $sign = $this->createSign($request, $this->app_key);
        $responseTxt = $this->getResponse($request['notify_id']);
        if (preg_match("/true$/i", $responseTxt) && $sign == $request['sign']) {
            //return 'success'; //效验出错
            return true;
        } else {
            // return 'fail';
            return false;
        }
    }

    function create_direct_pay_by_user($parameters) {
        $apiName = 'create_direct_pay_by_user';
        $param_key = array(
            'out_trade_no', 'subject', 'payment_type', 'total_fee', 'seller_id'
        );
        $other_param_key = array(
            'buyer_id', 'buyer_email', 'buyer_account_name',
            'quantity', 'body', 'payment_type', 'price',
            'anti_phishing_key', //钓鱼时间戳 业务必填
            'exter_invoke_ip',
            'it_b_pay',
            'qr_pay_mode',
            'qrcode_width',
            'need_buyer_realnamed',
            'promo_param',
            'hb_fq_param',
            'goods_type',
            'return_url',
            'notify_url',
            'error_notify_url',
        );




        $parameters['seller_id'] = isset($parameters['seller_id']) ? $parameters['seller_id'] : $this->partner;

        $api_params = array();
        $error_param = array();
        foreach ($param_key as $key) {
            if (isset($parameters[$key]) && !empty($parameters[$key])) {
                $api_params[$key] = $parameters[$key];
            } else {
                $error_param[] = $key;
            }
        }

        foreach ($other_param_key as $key) {
            if (isset($parameters[$key]) && !empty($parameters[$key])) {
                $api_params[$key] = $parameters[$key];
            }
        }

        if (!empty($error_param)) {
            //返回提示错误
            return;
        }
        if (!isset($api_params['return_url']) && $this->return_url != '') {
            $api_params['return_url'] = $this->return_url;
        }
        if (!isset($api_params['notify_url']) && $this->notify_url != '') {
            $api_params['notify_url'] = $this->notify_url;
        }
        if (!isset($api_params['error_notify_url']) && $this->notify_url != '') {
            $api_params['error_notify_url'] = $this->notify_url;
        }

        return $this->newHandle($apiName, $api_params);
    }

    /**
     * @param $apiName
     * @param $parameters
     * @return array
     */
    public function newHandle($apiName, $parameters) {
        $arr = $this->createArrayParam($parameters);
        $arr['service'] = $apiName;
        $arr['sign'] = $this->createSign($arr, $this->app_key);

        //return_url
        //正式
        $url = $this->alipay_gateway_new;

        $arg_arr = array();
        foreach ($arr as $key => $val) {
            $arg_arr[] = $key . "=" . urlencode($val);
        }
        //去掉最后一个&字符
        $arg = implode("&", $arg_arr);

        $url = $url . $arg;
//        var_dump($arg_arr);
//        echo $url;
//        die;
        //记录日志
        header('Content-Type: text/html;charset = UTF-8');
        return $url;
    }

    function getResponse($notify_id) {

        $partner = $this->partner;
        $veryfy_url = $this->https_verify_url;

        $veryfy_url = $veryfy_url . "partner=" . $partner . "&notify_id=" . $notify_id;
        $cacert = ROOT_PATH . 'lib/apiclient/cacert.pem';
        $responseTxt = $this->getHttpResponseGET($veryfy_url, $cacert);

        return $responseTxt;
    }

    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * return 远程输出的数据
     */
    function getHttpResponseGET($url, $cacert_url) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); //严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacert_url); //证书地址
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
        $logPath = $this->get_log_path();

        error_log(date("Y-m-d H:i:s") . "{$url}:\n{$responseText}\n", 3, $logPath);
        return $responseText;
    }

    function get_log_path() {
        static $logPath = NULL;
        if ($logPath === NULL) {
            $date = date("Y-m-d");
            $logPath = ROOT_PATH . "logs" . DIRECTORY_SEPARATOR;
            $logPath .= "http_client_alipay_" . $date . ".log";
   
        }
        return $logPath;
    }

    /**
     * 合并系统参数
     * @param $param
     * @return array
     */
    function createArrayParam($param) {
        $paramArr = array(
            'partner' => $this->partner,
            '_input_charset' => 'utf-8',
            'sign_type' => 'MD5',
        );

        return array_merge($paramArr, $param);
    }

    /**
     * 签名函数
     * @param $paramArr
     * @param $appkey
     * @return string
     */
    function createSign($paramArr, $appkey) {

        ksort($paramArr);
        reset($paramArr);
        $sign_arr = array();
        $no_sign_key = array('sign', 'sign_type', 'fastappsid');
        foreach ($paramArr as $key => $val) {
            if (!in_array($key, $no_sign_key) && $val != "") {
                $sign_arr[] = $key . '=' . $val;
            }
        }

        $sign = implode("&", $sign_arr) . $appkey;
        $sign = md5($sign);
        return $sign;
    }

}
