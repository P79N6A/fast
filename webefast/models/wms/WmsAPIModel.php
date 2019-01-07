<?php

require_lib('net/HttpClient');

/**
 * 第三方平台API抽象类，定义若干业务接口
 *
 * @author jhua.zuo<jhua.zuo@baisonmail.com>
 * @date 2015-03-09
 */
abstract class WmsAPIModel {
    abstract public function request_send($api, $param = array());
    
    abstract public function get_response($result);
    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    abstract public function sign($param);
        #####################################################################

    /**
     * PHP发送CURL请求
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @param string $url 请求地址
     * @param array $params 请求参数
     * @since efast3
     * @deprecated since version efast3
     * @return 返回请求结果
     */
    protected function makeRequest($url, $params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        /**
         * 如果参数为数组则
         */
        if (is_array($params) && 0 < count($params)) {
            $postBodyString = "";
            foreach ($params as $k => $v) {
                $postBodyString .= "$k=" . urlencode($v) . "&";
            }
            unset($k, $v);
        } else {
            $postBodyString = "";
        }

        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($postBodyString)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
        }

        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {
            $curl_error = curl_error($ch);
            throw new runtimeException($curl_error, -10);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new runtimeException("httpStatusCode={$httpStatusCode} " . $reponse, -11);
            }
        }
        curl_close($ch);
        return $reponse;
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
        try{
        $h = new HttpClient();
        $h->newHandle('0', $type, $url, $headers, $parameters,$other);
        $h->exec();

        $result = $h->responses();
            if (!isset($result['0'])) {
                throw new Exception('请求出错, 返回结果错误');
            }
        }  catch (Exception $ex){
            return $ex->getMessage();
        }

        return $result['0'];
    }

    /**
     * 执行批量API请求
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param string $url 请求的URL地址
     * @param array $parameters_array 请求参数数组, 相同地址的不同参数
     * @param string $type post or get
     * @param array $headers header信息
     * @return mixed
     * @throws Exception
     * @see lib/apiclient/ApiClient::multiExec
     * @depends lib/net/HttpClient
     */
    public function multiExec($url, $parameters_array, $type = 'post', $headers = array()) {
        $h = new HttpClient();
        foreach ($parameters_array as $key => $parameters) {
            $h->newHandle($key, $type, $url, $headers, $parameters);
        }
        $h->exec();
        return $h->responses();
    }

    /**
     * 将数组生成请求字符串
     * @auhtor jhua.zuo<jhua.zuo@baisonmail>
     * @date 2015-03-10
     * @param array $paramArr
     * @return string
     * @link http://open.taobao.com/doc/detail.htm?spm=a219a.7386781.1998343697.6.JblVVr&id=131
     */
    protected function createStrParam($paramArr) {
        $strParam = '';
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $strParam .= $key . '=' . urlencode($val) . '&';
            }
        }
        return $strParam;
    }

    /**
     * 对象转化为数组
     * @auhtor jhua.zuo<jhua.zuo@baisonmail>
     * @date 2015-03-10
     * @since efast3
     * @param object $e 待处理对象
     * @return array
     */
    protected function object_to_array($e) {
        $e = (array) $e;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }

            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $e[$k] = (array) $this->object_to_array($v);
            }
        }
        return $e;
    }

    /**
     * xml转数组
     * @param $data
     * @return array
     * @throws Exception
     */
    protected function xml2array($data) {
        require_lib('util/xml_util');
        $return = array();
        xml2array($data, $return);
        return $return;
    }
     /**
     * 数组转xml
     * @param $data
     * @return array
     * @throws Exception
     */
    protected function array2xml($data,$root_tag=NULL, $xmlns='') {
        require_lib('util/xml_util');
       return array2xml($data, $root_tag, $xmlns);
    }
   
    /**
     * 优化的json_decode方法， 保证5.3版本以下PHP能本地调试通过
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-13
     * @param string $result
     * @return array 返回json_decode以后的数组
     */
    protected function json_decode($result) {
    	if(PHP_VERSION>=5.4){
    		$return = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
    	}else{
    		$return = json_decode($result, true);
    	}
    	return $return;
    }
    
        function format_ret($status, $data = '', $msg = NULL) {

            return array(
                'status' => $status,
                'data' => $data,
                'message' => $msg
            );
    }
    
    function get_log_key(){
        return date('Y-m-d H:i:s').'#'.uniqid();
    }
    function set_log($log_key,$log_arr,$wms_type){
                $data_str = date('Y-m-d');
               $_log_file = ROOT_PATH.'webefast/logs/'.$wms_type.'_api_'.$data_str.'.log';//按天存
                
              foreach($log_arr as $key =>$data){
                error_log("\n--{$log_key} {$key}--\n".var_export($data,true),3,$_log_file);
              }
        
    }
}

