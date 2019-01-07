<?php
/**
 * 本文件包括HttpEx类和实用函数request_url，get_client_ip，get_raw_post_data，set_http_status
 */
/**
 * 访问网站类，包括get,post,mutilpost（多连接，可明显提高速度）
 * @author zengjf
 */
class HttpEx{
	private $url;
	private $timeout;
	/**
	 * @var int 错误号
	 */
	public $errno;
	/**
	 * @var string 错误消息
	 */
	public $errmsg;
	/**
	 * @var int 连接超时秒数
	 */
	public $connTimeout;
	/**
	 * 对象构建
	 * @param string $url	访问URL
	 * @param int $timeout 超时秒数  默认30s
	 */
	function __construct($url,$timeout=30){
		$this->url=$url;
		$this->timeout=$timeout;
		$this->errno=0;
		$this->errmsg=NULL;
	}
	/** 
	 * get $url，并返回结果
	 * @return array|string 如果成功返回结果字符串，失败返回array($errno,$errmsg)数组
	 */
	function get(){
	 		$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $this->url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_USERAGENT, 'fastapp/2.0');
			curl_setopt($curl, CURLOPT_ENCODING, ''); 
			
			if($this->connTimeout) curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connTimeout);
			else curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			curl_setopt($curl, CURLOPT_TIMEOUT, $this->connTimeout);
			
			$result=curl_exec($curl);
			if($result===FALSE){
				$this->putError($curl);
				curl_close($curl);
				return false;
			}
			curl_close($curl);  
			return $result;	
	}
	private function putError($curl){
		global $context;
		$this->errno=curl_errno($curl);		
		$this->errmsg=curl_error($curl);
		$context->log_error("httpex network  error:{$this->errno},{$this->errmsg}");		
	}
	/**
	* 触动touch服务器URL，不用保持活动连接
	*/
	function touch(){
		$a=parse_url($this->url);
		$host=$a['host'];
		$port= isset($a['port']) && $a['port']? $a['port'] : ($a['scheme'] == 'https' ? 443 : 80);
		$uri=  isset($a['query ']) && $a['query '] ? ( $a['path'].'?'. $a['query '] ) : 
				(isset($a['path']) && $a['path']? $a['path'] :'/' );
		$fp = fsockopen($host, $port, $err_no, $err_msg, $this->timeout);
		if($fp){
			stream_set_timeout($fp, 5);
			fwrite($fp, "GET {$uri} HTTP/1.1\r\nHost: {$host}\r\nUser-Agent: fastapp/2.0\r\nConnection: Close\r\n\r\n");
			fclose($fp);
			return true;
		}
		return false;
	}
	
	/** 
	 * post $data数据到$url，并返回结果
	 * @param string|array $data  post数据
	 * @return array|string 如果成功返回结果字符串，失败返回array($errno,$errmsg)数组
	 */
	function post($data){
			global $context;
	 		$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $this->url);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			curl_setopt($curl, CURLOPT_USERAGENT, 'fastapp/2.0');
			curl_setopt($curl, CURLOPT_ENCODING, ''); 		
			curl_setopt($curl, CURLOPT_POST,true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			$result=curl_exec($curl);
			if($result===FALSE){
				$this->putError($curl);
				curl_close($curl);
				return false;
			}
			curl_close($curl);  
			return $result;	
	}
	/**
	 * 多连接post，可提高速度， $data数据到$url，并返回结果
	 * @param array $dataList post数据列表，key为taskid，value为本行数据数组
	 * @param callback $callback  回调函数，func($taskid,$resp,&$call_back_data)，
	 * $taskid为对应$dataList单行taskid，$resp为post结果字符串，$call_back_data为函数传入的参数。
	 * @param $call_back_data 传入参数，将传递给$callback函数。
	 */
	function postMutilCallback(array $dataList,$callback,&$call_back_data) {
		global $context;
		$chs = array();
		$mh = curl_multi_init();
		foreach($dataList as $taskid=>$data) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $this->url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			curl_setopt($curl, CURLOPT_USERAGENT, 'fastapp/2.0');
			curl_setopt($curl, CURLOPT_ENCODING, ''); 			
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_multi_add_handle($mh, $curl);
	
			$chs[] = array($curl, -1, $taskid);
		}
		$active = null;
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while($mrc == CURLM_CALL_MULTI_PERFORM);
	
		while($active && $mrc == CURLM_OK) {
			if(curl_multi_select($mh) != -1) {
				do {
					$mrc = curl_multi_exec($mh, $active);
				} while($mrc == CURLM_CALL_MULTI_PERFORM);
			}		
			while(false !== ($info = curl_multi_info_read($mh))) {
				for($i=0;$i<count($chs);$i++ ){
					if($chs[$i][0] == $info ['handle']) {
				 		$resp= curl_multi_getcontent($chs[$i][0]);
				 		$errno=curl_errno($chs[$i][0]);
						if($errno)	$this->putError($chs[$i][0]);
				 		if(is_array($callback))
				 			$callback[0]->$callback[1]($chs[$i][2],$resp,$call_back_data);
				 		else $callback($chs[$i][2],$resp,$call_back_data);
						curl_multi_remove_handle($mh, $chs[$i][0]);
						$chs[$i][1] = 1;
					}
				}
			}
	
		}
		foreach($chs as $ch) {
			if($ch [1] == -1) {
				 $resp= curl_multi_getcontent($ch[0]);
				 $errno=curl_errno($ch[0]);
				 if($errno)	$this->putError($ch[0]);
				 if(is_array($callback))
				 	$callback[0]->$callback[1]($ch[2],$resp,$call_back_data);
				 else $callback($ch[2],$resp,$call_back_data);
				curl_multi_remove_handle($mh, $ch[0]);
				curl_close($ch [0]);
			}
		}
		unset($ch);
		curl_multi_close($mh);
	}
	private function _mutil_post_callback($taskid,$resp,&$result){
		$result[$taskid] = & $resp;
	}	
	/**
	 * 多连接post,可提高速度， $data数据到$url，并返回结果
	 * @param array $dataList post数据列表，key为taskid，value为本行数据数组
	 * @param array $result post结果列表，key为taskid，value为本行post返回结果
	 */
	function postMutil(array $dataList,array &$result) {
		$this->postMutilCallback($dataList,array($this,'_mutil_post_callback'),$result);
	}
}
/**
 * 快捷函数
 * @param string $url  访问URL
 * @param string|array $data  post数据  ，如果为NULL，使用get方法，否则post
 * @param int $errno	 错误号
 * @param string $errmsg 错误消息
 * @return false|string  返回结果，如果为false,失败，否则成功。 
 */
function do_http($url,$data,&$errno,&$errmsg){
	$c=new HttpEx($url);
	if($data) $r=$c->post($data);
	else $r=$c->get();
	if($r===false){
		$errno=$c->errno;
		$errmsg=$c->errmsg;
	}
	return $r;
}
/**
 * 获取客户端正在访问页面的url
 * @param booelan $is_uri 是否包含URI路径 默认true
 * @param booelan $use_port 是否包含端口号 默认true，如果有代理服务器，而且代理服务器没有设置好SERVER_PORT，可能导致端口号错误。
 * @return string 对应url
 */
function request_url($is_uri=true,$use_port=true) {
	$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';

	if(isset($_SERVER['SERVER_PORT'])){
		 $port=NULL;
		 if($_SERVER['SERVER_PORT'] == '80') $protocol='http://';
		 elseif($_SERVER['SERVER_PORT'] == '443') $protocol='https://';
		 else{
		 	$protocol='http://';
		 	$port=$_SERVER['SERVER_PORT'];
		 }
	}
	if (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && $_SERVER['HTTP_X_FORWARDED_HOST'] )
		$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ;
	elseif(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'])
		$host = $_SERVER['HTTP_HOST'] ;
	else $host =$_SERVER['SERVER_NAME'];
	
	$url= $protocol.$host;
	if($use_port && $port) $url .= ':' . $port;
	if($is_uri)	$url .=	$_SERVER["REQUEST_URI"];
	return $url;
}
if(! function_exists('get_client_ip') ){
	/**
	 * 返回浏览器的IP地址，依次查找HTTP_CLIENT_IP、HTTP_X_FORWARDED_FOR、REMOTE_ADDR的值，如果找不到IP，返回NULL
	 */
	function get_client_ip(){
   		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
      		return getenv("HTTP_CLIENT_IP");
   		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
       		return getenv("HTTP_X_FORWARDED_FOR");
   		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
      		return getenv("REMOTE_ADDR");
   		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
       		return $_SERVER['REMOTE_ADDR'];
   		else
       		return NULL;
	}
}
/**
 * @return 返回浏览器post的原始数据，除常规key=value外，还可以非常规数据，如原生xml，json数据等。
 */
function & get_raw_post_data(){
	$s= file_get_contents("php://input");
	return $s;
}
/**
 * 设置http返回状态
 * @param integer $code http状态码
 */
function set_http_status($code) {
    static $_http_status = array(
        // Informational 100
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 200
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 300
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        //client Error 400
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        //server Error 500
        500 => 'Internal Server Error', 
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    if(array_key_exists($code,$_http_status)) {
        header("HTTP/1.1 {$code} {$_http_status[$code]}");
    }
}
