<?php
//主要是通过接口取店铺的信息
class YihaodianModel extends TbModel {
    
    var $app_key = '10210015011400002866';
    var $app_secret = '6b2109abe40979c4acb41dbfc9068724';
    var $app_url = 'http://openapi.yhd.com/app/api/rest/router';
    
    public function do_execute_get($url, $params) {
        /**
         * 如果参数为数组则
         */
        if (is_array ( $params ) && 0 < count ( $params )) {
                $postBodyString = "";
                foreach ( $params as $k => $v ) {
                        $postBodyString .= "$k=" . urlencode ( $v ) . "&";
                }
                unset ( $k, $v );
                if($postBodyString){
                        $postBodyString = substr($postBodyString, 0, -1);
                }
        } else {
                $postBodyString = $params;
        }

        $url_get = $url.$postBodyString;

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url_get );
        curl_setopt ( $ch, CURLOPT_FAILONERROR, false );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 45);


        $logs_arr = array();
        $logs_arr['url'] = $url;
        $logs_arr['param'] = json_encode($param);

        $reponse = curl_exec ( $ch );
        if (curl_errno ( $ch )) {
                $curl_error = curl_error ( $ch );
                throw new Exception ( $curl_error, 0 );
        } else {
                $httpStatusCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
                if (200 !== $httpStatusCode) {
                        throw new Exception ( $reponse, $httpStatusCode );
                }
        }
        curl_close ( $ch );

        return $reponse;
    }
    
    function do_execute($url, $params = array()) {
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_FAILONERROR, false );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 30);
        /**
         * 如果参数为数组则
         */
        if (is_array ( $params ) && 0 < count ( $params )) {
                $postBodyString = "";
                foreach ( $params as $k => $v ) {
                        $postBodyString .= "$k=" . urlencode ( $v ) . "&";
                }
                unset ( $k, $v );
        } else {
                $postBodyString = "";
        }

        curl_setopt ( $ch, CURLOPT_POST, true );
        if(!empty($postBodyString)){
                curl_setopt ( $ch, CURLOPT_POSTFIELDS, substr ( $postBodyString, 0, -1 ) );
        }

        $reponse = curl_exec ( $ch );

        if (curl_errno ( $ch )) {
                $curl_error = curl_error ( $ch );
                throw new Exception ( $curl_error, 0 );
        } else {
                $httpStatusCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
                if (200 !== $httpStatusCode) {
                        throw new Exception ( $reponse, $httpStatusCode );
                }
        }
        curl_close ( $ch );

        return $reponse;
    }
    
    private function createSign($app_secret, $paramArr) {
        $sign = $app_secret;

        ksort($paramArr);

        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $sign .= $key . $val;
            }
        }

        $sign .= $app_secret;

        $sign = md5($sign);

        return $sign;
    }
    
    private function _getParamArr($method, $params) {
        //参数数组
        $paramSys = array(
            'ver'=>'1.0',       //接口版本1.0
            'method'=>$method,    //接口名称
            'timestamp' => date('Y-m-d H:i:s'),  //时间戳
            'appKey' => $this->app_key,
            'format'=>'json',
        );

        $paramArr = array_merge($paramSys, $params);

        //生成签名
        $sign = $this->createSign($this->app_secret, $paramArr);

        $paramArr['sign'] = $sign;

        return $paramArr;
    }
    
    
    function get_result($method, $params) {
        $paramArr = $this->_getParamArr($method, $params);
        $result = $this->do_execute($this->app_url, $paramArr);
        $result = json_decode($result,true);
        if(empty($result)){
            return $this->format_ret(-1,'','调用接口失败：'.$result);
        }
        if ($result['response']['errorCount'] > 0) {
            $err_msg = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
            return $this->format_ret(-1,'',$err_msg);
        }
        return $this->format_ret(1,$result);
    }
    
    function get_api_shop_title($access_token){
        $params = array('sessionKey'=>$access_token);
        $ret = $this->get_result('yhd.store.get',$params);

        if($ret['status']<0){
            return $ret;
        }

        $shop_name = @$ret['data']['response']['storeMerchantStoreInfo']['storeName'];
        if(empty($shop_name)){
            return $this->format_ret(-1,'','找不到店铺名称');
        }
        return $this->format_ret(1,$shop_name);
    }
}