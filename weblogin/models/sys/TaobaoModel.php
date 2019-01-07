<?php
//主要是通过接口取店铺的信息
class TaobaoModel extends TbModel {
	var $app_key = '12651526';
	var $app_secret = '11b9128693bfb83d095ad559f98f2b07';
	var $app_session = '6101f071e681efa16d78b17f09d32245176bd46979402e758123788';
    var $app_url = 'http://gw.api.taobao.com/router/rest?';//
      //  var $conf = array();
        function set_app($app_key){
            $app_info = require_conf('app_info');
            $conf =$app_info['taobao'][$app_key] ; 
            $this->app_key = $conf['app_key'];
            $this->app_secret = $conf['app_secret'];
        }



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
		//echo '<hr/>url_get<xmp>'.var_export($url_get,true).'</xmp>';

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
		//echo '<hr/>url<xmp>'.var_export($url,true).'</xmp>';
		//echo '<hr/>params<xmp>'.var_export($params,true).'</xmp>';
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
                //日志
                $logPath = ROOT_PATH."logs/http_client-".date("Ymd").".log";
                error_log(date("Y-m-d H:i:s")."\n url:".$url."\n params:".var_export($params,TRUE)."\n\n", 3, $logPath);
                
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
                
                //日志
                error_log(date("Y-m-d H:i:s")."\n req:".$reponse, 3, $logPath);
        /*
        echo "网址=".$url."\n<br/>";
        echo "参数=".var_export($params,true)."\n<br/>";
        echo "返回=".$reponse."\n<br/>\n\n<br/>";
        */
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

        $sign = strtoupper(md5($sign));

        return $sign;
    }

    private function _getParamArr($method, $params) {
        //参数数组
        $paramSys = array('method' => $method, 'timestamp' => date('Y-m-d H:i:s'), 'app_key' => $this->app_key, 'format' => 'json', 'v' => '2.0', 'sign_method' => 'md5');

        if ($this->app_session !== '') {
            $paramSys ['session'] = $this->app_session;
        }

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
        if(isset($result['error_response'])){
            $err_msg = $result['error_response']['sub_msg'];
            if(empty($err_msg)){
                $err_msg = $result['error_response']['msg'];
            }
            return $this->format_ret(-1,'',$err_msg);
        }
        return $this->format_ret(1,$result);
    }
}