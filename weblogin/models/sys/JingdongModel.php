<?php
//主要是通过接口取店铺的信息
class JingdongModel extends TbModel {
	var $app_key = 'C0C9110ED2D32E13266D4522D55C78AF';
	var $app_secret = 'f58736a321bd44fabf2253f5d25344f0';
   // var $app_url = 'http://gw.api.jd.com/routerjson';
        var $app_url = 'https://api.jd.com/routerjson';
      function set_app($app_key){
            $app_info = require_conf('app_info');
            $conf =$app_info['jingdong'][$app_key] ; 
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
        $paramSys = array(
            'v'=>'2.0',
            'method'=>$method,
            'timestamp' => date('Y-m-d H:i:s'),
            'app_key' => $this->app_key
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
        if(isset($result['error_response'])){
            $err_msg = $result['error_response']['zh_desc'];
            if(empty($zh_desc)){
                $err_msg = $result['error_response']['en_desc'];
            }
            return $this->format_ret(-1,'',$err_msg);
        }
        return $this->format_ret(1,$result);
    }

    function get_api_shop_title($access_token){
        //$access_token = 'f4320e32-8a58-4665-8e67-82ce8e5f9c9b';
        $params = array('access_token'=>$access_token);
        $ret = $this->get_result('jingdong.seller.vender.info.get',$params);
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        if($ret['status']<0){
            return $ret;
        }
       // $shop_name = @$ret['data']['jingdong_seller_vender_info_get_responce']['vender_info_result']['shop_name'];
        $vender_info = $shop_name = @$ret['data']['jingdong_seller_vender_info_get_responce']['vender_info_result'];
        if(empty($vender_info)){
            return $this->format_ret(-1,'','找不到店铺名称');
        }
        /**
       	 商家类型:
        0：SOP 1：FBP 2：LBP 5：SOPL
        */
        $type_arr = array('0' => 'sop','1'=>'fbp','2'=>'lbp','5'=>'sopl');
        $vender_info['type'] = $type_arr[$vender_info['col_type']];
        return $this->format_ret(1,$vender_info);
    }

}