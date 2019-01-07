<?php

class taobao_util {
	public $topUrl = '223.4.54.191/taobao_trans_req.php?'; //生产环境
	// private $topUrl = 'http://gw.api.taobao.com/router/rest?';   //生产环境
	//private $topUrl = 'http://synccenter.taobao.com/api?';       //淘宝内部测试环境
	//private $topUrl = 'http://gw.api.tbsandbox.com/router/rest?';  //沙箱环境

	private $appKey = '';

	private $appSecret = '';

	private $appSession = '';

	private $appNick = '';

	public function __construct($appKey = '', $appSecret = '', $appSession = '', $appNick = '') {
		$this->appKey = $appKey;
		$this->appSecret = $appSecret;
		$this->appSession = $appSession;
		$this->appNick = $appNick;
	}

	public function init_by_shop($shopCode) {
		$api = load_model('base/distributor')->get_shop_api_by_shop_code($shopCode);
		if (empty($api)) {
			return false;
		}

		$this->appKey = $api['app'];
		$this->appSecret = $api['secret'];
		$this->appSession = $api['session'];
		$this->appNick = $api['nick'];

		return true;
	}

	public function demo() {
		require_lib('util/taobao_util');
		$taobao = new taobao_util();

		$taobao->init_by_shop('001');

		$params = array();
		$params[''] = '';
		$data = $taobao->post('taobao.trade.memo.update', $params);
		if ($data['status'] != '1') {
			//错误处理
		}

		//正常业务处理
	}

	/**
	 * 返回json格式数据, 通过GET方式
	 * @param mixed $method
	 * @param mixed $params
	 * @param mixed $debugResult
	 * @return bool
	 */
	public function get($method, $params, & $debugResult = null) {
		$requestUrl = $this->createUrl($method, $params);

		if (DEBUG)
			$this->log($requestUrl);

		try {
			$debugResult = $this->curl($requestUrl);
		} catch (Exception $e) {
			$debugResult = $e->getMessage();
			return false;
		}

		return $this->jsonResult($method, $debugResult);
	}

	/**
	 * 返回json格式数据, 通过Post方式
	 *
	 * @param mixed $method
	 * @param mixed $params
	 * @return bool
	 */
	public function post($method, $params) {
		$sysParams = array();
//	    $sysParams["app_key"] = $this->appKey;
//	    $sysParams["v"] = '2.0';
//	    $sysParams["format"] = 'json';
//	    $sysParams["sign_method"] = 'md5';
//	    $sysParams["method"] = $method;
//	    $sysParams["timestamp"] = date("Y-m-d H:i:s");
		//组装系统参数
		$params["app_key"] = $this->appKey;
		$params["v"] = '2.0';
		$params["format"] = 'json';
		$params["sign_method"] = 'md5';
		$params["method"] = $method;
		$params["timestamp"] = date("Y-m-d H:i:s");
		//$sysParams["partner_id"] = $this->sdkVersion;
		if (!empty($this->appSession)) {
			//     $sysParams["session"] = $this->appSession;
			$params["session"] = $this->appSession;
		}

		//签名
//	    $sysParams["sign"] = $this->generateSign(array_merge($params, $sysParams));
		$params["sign"] = $this->generateSign(array_merge($params, $sysParams));

		//系统参数放入GET请求串
		$requestUrl = $this->topUrl;
//        foreach ($sysParams as $sysParamKey => $sysParamValue)
//        {
//            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
//        }

		foreach ($params as $sysParamKey => $sysParamValue) {
			$requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
		}

		$requestUrl = substr($requestUrl, 0, -1);

		if (DEBUG) {
			$this->log($requestUrl);
			$this->log(var_export($params, true));
		}

		//发起HTTP请求
		try {
			$debugResult = $this->curl($requestUrl, $params);
		} catch (Exception $e) {
			return $this->return_value('-1', $e->getMessage(), array());
		}

		return $this->jsonResult($method, $debugResult);
	}

	######## 私有方法 ########################################################

	/**
	 * 解析服务端返回值
	 *
	 * @param mixed $method
	 * @param mixed $debugResult
	 * @return bool
	 */
	private function jsonResult($method, &$debugResult) {
		if (empty($debugResult)) {
			return $this->return_value('-2', array(), 'Connect failed or network error.');
		}

		//科学计数法转换为字符串, php5.4可参数控制
		$debugResult = preg_replace('/([^\\\])(":)(\d{9,})/i', '${1}${2}"${3}"', $debugResult);

		$arr = json_decode($debugResult, true);

		if ($arr == NULL) { //json解析失败
			return $this->return_value('-3', 'JSON解析失败', $debugResult);
		}

		if (isset($arr['error_response'])) {
			$debugResult = $arr['error_response'];
			$err = isset($debugResult['sub_msg']) ? $debugResult['sub_msg'] : 'API返回未知错误';
			return $this->return_value('-4', $err, $debugResult);
		}

		$debugResult = NULL; //成功读取时, 不返回调试数据

		$key = substr($method, 7);
		$key = str_replace('.', '_', $key);
		$key = $key . '_response';

		return $this->return_value('1', '', $arr[$key]);
	}

	/**
	 * 构建签名, 替代createSign()
	 *
	 * @param mixed $params
	 * @return string
	 */
	protected function generateSign($params) {
		ksort($params);

		$stringToBeSigned = $this->appSecret;
		foreach ($params as $k => $v) {
			if ("@" != substr($v, 0, 1)) {
				$stringToBeSigned .= "$k$v";
			}
		}
		unset($k, $v);
		$stringToBeSigned .= $this->appSecret;

		return strtoupper(md5($stringToBeSigned));
	}

	/**
	 * 签名
	 *
	 * @param mixed $appSecret
	 * @param mixed $paramArr
	 * @return string
	 */
	private function createSign($appSecret, $paramArr) {
		$sign = $appSecret;

		ksort($paramArr);

		foreach ($paramArr as $key => $val) {
			if ($key != '' /*&& $val != ''*/) {
				$sign .= $key . $val;
			}
		}

		$sign .= $appSecret;

		$sign = strtoupper(md5($sign));

		return $sign;
	}

	/**
	 * 组参
	 *
	 * @param mixed $paramArr
	 * @param mixed $sign
	 * @return string
	 */
	private function createStrParam($paramArr, $sign) {
		$strParam = '';

		foreach ($paramArr as $key => $val) {
			if ($key != '' /*&& $val != ''*/) {
				$strParam .= $key . '=' . urlencode($val) . '&';
			}
		}

		//sign
		$strParam .= 'sign=' . $sign;

		return $strParam;
	}

	/**
	 * URL
	 *
	 * @param mixed $method
	 * @param mixed $params
	 * @return string
	 */
	private function createUrl($method, $params) {
		//参数数组
		$paramSys = array(
			'method' => $method,
			'timestamp' => date('Y-m-d H:i:s'),
			'app_key' => $this->appKey,
			'format' => 'json',
			'v' => '2.0',
			'sign_method' => 'md5'
		);

		if ($this->appSession !== '') {
			$paramSys['session'] = $this->appSession;
		}

		$paramArr = array_merge($paramSys, $params);

		//生成签名
		$sign = $this->createSign($this->appSecret, $paramArr);

		//组织参数
		$strParam = $this->createStrParam($paramArr, $sign);

		//URL
		return $this->topUrl . $strParam;
	}

	public function curl($url, $postFields = null) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//https 请求
		if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}

		if (is_array($postFields) && 0 < count($postFields)) {
			$postBodyString = "";
			$postMultipart = false;
			foreach ($postFields as $k => $v) {
				if ("@" != substr($v, 0, 1)) //判断是不是文件上传
				{
					$postBodyString .= "$k=" . urlencode($v) . "&";
				} else //文件上传用multipart/form-data，否则用www-form-urlencoded
				{
					$postMultipart = true;
				}
			}
			unset($k, $v);
			curl_setopt($ch, CURLOPT_POST, true);
			if ($postMultipart) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
			} else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
			}
		}
		$reponse = curl_exec($ch);

		if (DEBUG)
			$this->log($reponse, "\r\n");

		if (curl_errno($ch)) {
			throw new Exception(curl_error($ch), 0);
		} else {
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode) {
				throw new Exception($reponse, $httpStatusCode);
			}
		}
		curl_close($ch);
		return $reponse;
	}

	private function log($var, $end = '') {
		error_log(date('Y-m-d H:i:s') . ': ' . $var . $end . "\r\n", 3, ROOT_PATH . '/logs/taobao_' . date('Y-m-d') . '.log');
	}

	private function return_value($status, $message = '', $data = '') {
		return array('status' => $status, 'message' => $message, 'data' => $data);
	}
}
