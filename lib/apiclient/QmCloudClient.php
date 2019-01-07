<?php

/**
 * QmErpApiClient类
 * @author zwj
 * Date: 1/4/18
 * Time: 11:34 AM
 */
class QmCloudClient {

    public $appKey = "23300032";

    public $secretKey = "fc0c155345cf996ba9257bc7bd877770";

    public $targetAppKey = "23316736";

    public $customer_id = "";

    //public $gatewayUrl = 'http://qimen.api.taobao.com/router/qm'; //正式
    public $gatewayUrl = 'http://qimen.api.taobao.com/router/qmtest'; //测试

    public $format = "json";

    public $connectTimeout;

    public $readTimeout;

    /** 是否打开入参check**/
    public $checkRequest = true;

    protected $signMethod = "md5";

    protected $apiVersion = "2.0";

    protected $sdkVersion = "top-sdk-php-20151012";

    public function __construct($api_config) {
        $this->appKey = $api_config['api_key'] ? $api_config['api_key'] : $this->appKey;
        $this->secretKey = $api_config['api_secret'] ? $api_config['api_secret'] : $this->secretKey;
        $this->targetAppKey = $api_config['target_key'] ? $api_config['target_key'] : $this->targetAppKey;
        $this->gatewayUrl = $api_config['api_url'] ? $api_config['api_url'] : $this->gatewayUrl;
        $this->customer_id = $api_config['customer_id'] ? $api_config['customer_id'] : $this->customer_id;
    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    protected function generateSign($params){
        ksort($params);
        $stringToBeSigned = $this->secretKey;
        foreach ($params as $k => $v)
        {
            if(is_string($v) && "@" != substr($v, 0, 1))
            {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->secretKey;

        return strtoupper(md5($stringToBeSigned));
    }

    /**
     * PHP发送CURL请求
     * @author zwj
     * @date 2018-01-04
     * @param string $url 请求地址
     * @param array $params 请求参数
     * @since efast
     * @deprecated since version efast
     * @return 返回请求结果
     */
    public function curl($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->readTimeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->readTimeout);
        }
        if ($this->connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }
        curl_setopt ( $ch, CURLOPT_USERAGENT, "top-sdk-php" );
        //https 请求
        if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if (is_array($postFields) && 0 < count($postFields))
        {
            $postBodyString = "";
            $postMultipart = false;
            foreach ($postFields as $k => $v)
            {
                if(!is_string($v))
                    continue ;

                if("@" != substr($v, 0, 1))//判断是不是文件上传
                {
                    $postBodyString .= "$k=" . urlencode($v) . "&";
                }
                else//文件上传用multipart/form-data，否则用www-form-urlencoded
                {
                    $postMultipart = true;
                    if(class_exists('\CURLFile')){
                        $postFields[$k] = new \CURLFile(substr($v, 1));
                    }
                }
            }
            unset($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart)
            {
                if (class_exists('\CURLFile')) {
                    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
                } else {
                    if (defined('CURLOPT_SAFE_UPLOAD')) {
                        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
                    }
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            }
            else
            {
                $header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
                curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
            }
        }
        $response = curl_exec($ch);

        if (curl_errno($ch))
        {
            throw new Exception(curl_error($ch),0);
        }
        else
        {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode)
            {
                throw new Exception($response,$httpStatusCode);
            }
        }
        curl_close($ch);
        return $response;
    }

    public function curl_with_memory_file($url, $postFields = null, $fileFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->readTimeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->readTimeout);
        }
        if ($this->connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }
        curl_setopt ( $ch, CURLOPT_USERAGENT, "top-sdk-php" );
        //https 请求
        if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        //生成分隔符
        $delimiter = '-------------' . uniqid();
        //先将post的普通数据生成主体字符串
        $data = '';
        if($postFields != null){
            foreach ($postFields as $name => $content) {
                $data .= "--" . $delimiter . "\r\n";
                $data .= 'Content-Disposition: form-data; name="' . $name . '"';
                //multipart/form-data 不需要urlencode，参见 http:stackoverflow.com/questions/6603928/should-i-url-encode-post-data
                $data .= "\r\n\r\n" . $content . "\r\n";
            }
            unset($name,$content);
        }

        //将上传的文件生成主体字符串
        if($fileFields != null){
            foreach ($fileFields as $name => $file) {
                $data .= "--" . $delimiter . "\r\n";
                $data .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $file['name'] . "\" \r\n";
                $data .= 'Content-Type: ' . $file['type'] . "\r\n\r\n";//多了个文档类型

                $data .= $file['content'] . "\r\n";
            }
            unset($name,$file);
        }
        //主体结束的分隔符
        $data .= "--" . $delimiter . "--";

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER , array(
                'Content-Type: multipart/form-data; boundary=' . $delimiter,
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        unset($data);

        if (curl_errno($ch))
        {
            throw new Exception(curl_error($ch),0);
        }
        else
        {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode)
            {
                throw new Exception($response,$httpStatusCode);
            }
        }
        curl_close($ch);
        return $response;
    }

    /**
     * 执行单个API请求
     * @author zwj
     * @date 2018-01-04
     * @param string $url 请求的URL地址
     * @param array $parameters 请求参数
     * @param string $type post or get
     * @param array $headers header信息
     * @return mixed
     * @throws Exception
     */
    public function execute($method, $params = array())
    {
        $time1 = date("Y-m-d H:i:s");
        $time_num = time();
        //组装系统参数
        $sysParams["app_key"] = $this->appKey;
        $sysParams["v"] = $this->apiVersion;
        $sysParams["format"] = $this->format;
        $sysParams["sign_method"] = $this->signMethod;
        $sysParams["method"] = $method;
        $sysParams["timestamp"] = date("Y-m-d H:i:s");
        $sysParams["target_app_key"] = $this->targetAppKey;

        //获取业务参数
        $apiParams = $params;

        //系统参数放入GET请求串
        $requestUrl = $this->gatewayUrl."?";

        //签名
        $sysParams["sign"] = $this->generateSign(array_merge($apiParams, $sysParams));

        foreach ($sysParams as $sysParamKey => $sysParamValue)
        {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }

        $fileFields = array();
        foreach ($apiParams as $key => $value) {
            if(is_array($value) && array_key_exists('type',$value) && array_key_exists('content',$value) ){
                $value['name'] = $key;
                $fileFields[$key] = $value;
                unset($apiParams[$key]);
            }
        }

        $requestUrl = substr($requestUrl, 0, -1);

        //发起HTTP请求
        try
        {
            if(count($fileFields) > 0){
                $resp = $this->curl_with_memory_file($requestUrl, $apiParams, $fileFields);
            }else{
                $resp = $this->curl($requestUrl, $apiParams);
            }

            $time2 = date("Y-m-d H:i:s");
            $log_arr['url'] = $requestUrl;
            $log_arr['data'] = $apiParams;
            $req = var_export($log_arr, true);
            $res = var_export(json_decode($resp, true), true);
            $logPath = $this->get_log_path();

            $cha_time = time()-$time_num;
            error_log(date("Y-m-d H:i:s").":({$time1}-{$time2} 耗时：{$cha_time}) \n".$req."\n".$res."\n\n", 3, $logPath);
        }
        catch (Exception $ex)
        {
            return $ex->getMessage();
        }

        unset($apiParams);
        unset($fileFields);

        return json_decode($resp, true);
    }

    public function get_response($result) {
        if (empty($result)) {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $result);
        }
        if (isset($result['response']['flag']) && $result['response']['flag'] == 'failure') {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $result['response']['message'].','.$result['response']['sub_message']);
        }
        if (empty($result['response'])) {
            return $this->format_ret(-1, '', '接口返回数据有错.' . $result);
        }
        return $this->format_ret(1, $result['response']);
    }

    function format_ret($status, $data = '', $msg = NULL) {
        return array(
            'status' => $status,
            'data' => $data,
            'message' => $msg
        );
    }

    function  get_log_path(){
        static $logPath = NULL;
        if( $logPath === NULL ){
            $date = date("Y-m-d");
            $logPath = ROOT_PATH."logs".DIRECTORY_SEPARATOR;

            if (defined('RUN_SAAS') && RUN_SAAS) {
                $logPath .= "http_client".DIRECTORY_SEPARATOR;
                if (!file_exists($logPath)){
                    mkdir($logPath);
                }
                $logPath .= $date.DIRECTORY_SEPARATOR;
                if (!file_exists($logPath)){
                    mkdir($logPath);
                }
                $logPath .= "http_client_";
                $saas_key = CTX()->saas->get_saas_key();
                if (!empty($saas_key)) {
                    $logPath .= $saas_key."_";
                }

                $logPath .=$date.".log";
            }else{
                $logPath .= "http_client".$date.".log";
            }
        }
        return $logPath;
    }
}
