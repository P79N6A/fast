<?php
define('WECHAT_SEND_URL','https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=');

define('WECHAT_URL_PATH','https://api.weixin.qq.com/cgi-bin/');
define('WECHAT_TOKEN_URL','https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s');

define('WECHAT_CACHE_PATH','tencent/wechat/');
define('WECHAT_SESSION','wechat_');

class WeChat{
	public $appId;
	public $appSecret;
	/**
	 * @var string 微信访问令牌，调用微信接口需要使用
	 */
	private $accessToken;
	/**
	 * @var string 微信账户开发者OpenId
	 */
	public $openId;
	/**
	 * @var string 用户微信访问令牌，调用微信接口需要使用
	 */
	private $toAccessToken;	
	/**
	 * @var string 用户OpenId
	 */
	private $toOpenId;

	private $toCode;
	
	/**
	 * 服务器配置URL响应echostr时调用
	 * @param string $token  服务器配置Token
	 */
	static function echoStr($token){
		$echoStr 	= $_GET["echostr"];
	    $signature 	= $_GET["signature"];
        $timestamp 	= $_GET["timestamp"];
        $nonce 		= $_GET["nonce"];
        if( $echoStr && $signature && $timestamp && $nonce){ 
			$tmpArr = array($token, $timestamp, $nonce);
			sort($tmpArr);
			$tmpStr = implode( $tmpArr );
			$tmpStr = sha1( $tmpStr );
			if( $tmpStr == $signature ){
				echo $echoStr;
				exit;
			}	
        }
		exit;		
		 
	}
	/**
	 * 得到微信授权url
	 * @param string $redirect_uri 授权后重定向uri
	 */
	function getAuthUrl($redirect_uri,$state){
		$u='https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=%s';
		//$u='https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=%s';
		return sprintf($u,$this->appId,$redirect_uri,$state);
	}
	function authorize($code,& $toOpenId,& $toAccessToken){
		global $context;
		$u='https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';
		$r=$this->getWx(sprintf($u,$this->appId,$this->appSecret,$code));
		if($r===false) return;
		
		$toOpenId= $this->toOpenId= $r['openid'];
		$toAccessToken= $this->toAccessToken= $r['access_token'];

		$context->set_session(WECHAT_SESSION.'code',$code);
		$context->set_session(WECHAT_SESSION.'access_token',$this->toAccessToken);
		$context->set_session(WECHAT_SESSION.'refresh_token',$r['refresh_token']);
		$context->set_session(WECHAT_SESSION.'refresh_time',$r['expires_in']+time()-600);
		$context->set_session(WECHAT_SESSION.'openid',$this->toOpenId);
	}
	function getToOpenId(& $toCode,& $toOpenId,& $toAccessToken){
        if (!isset($_SESSION))  session_start();
		if($this->toOpenId){
			$toOpenId=$this->toOpenId;
			$toAccessToken=$this->toAccessToken;
			$toCode==$this->toCode;
			return true;
		}
		global $context;
		$this->toAccessToken=$context->get_session(WECHAT_SESSION.'access_token');
		$this->toOpenId		=$context->get_session(WECHAT_SESSION.'openid');	
		$this->toCode		=$context->get_session(WECHAT_SESSION.'code');	
		$rtoken				=$context->get_session(WECHAT_SESSION.'refresh_token');	
		$rtime				=$context->get_session(WECHAT_SESSION.'refresh_time');
		
		if($rtime>0 && $rtime<time()){
			$u='https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=%s&grant_type=refresh_token&refresh_token=%s';
			$r=$this->getWx(sprintf($u,$this->appId,$rtoken) );
			if($r!==false){
				$this->toAccessToken=$r['access_token'];
				$context->set_session(WECHAT_SESSION.'refresh_time',$r['expires_in']+time-3600);
				$context->set_session(WECHAT_SESSION.'refresh_token',$r['refresh_token']);
			}
		}
		
		if($this->toOpenId){
			$toOpenId		=$this->toOpenId;
			$toAccessToken	=$this->toAccessToken;
			$toCode			=$this->toCode;
			return true;			
		}	
		return false;	
	}
	/**
	 * 发送文本消息
	 * @param string $to   接收方账号OpenID
	 * @param string $text 回复的消息内容	（换行：在$text中能够换行，微信客户端就支持换行显示）
	 * @param string $from 开发者微信号OpenID，如果为空，使用类变量
	 * @return boolean  成功 true，参数为空 false
	 */
	function putText($to,$text,$from=NULL){
		return $this->putContent($to,$from,0,$text);
	}
	/**
	 * 发送单条图文消息
	 * @param string $toUser   	接收方账号OpenID
	 * @param string $title		标题
	 * @param string $desc		摘要
	 * @param string $picurl	图片URL
	 * @param string $url		消息URL，点击图文消息即跳转到此URL
	 * @param string $from 		开发者微信号OpenID，如果为空，使用类变量
	 * @return boolean  		成功 true，参数为空 false
	 */
	function putImageText($to,$title,$desc,$picurl,$url,$from=NULL){
		$m=array(array('title'=>$title,'desc'=>$desc,'picurl'=>$picurl,'url'=>$url));
		return $this->putMutilImageText($to,$m,$from);
	}	
	/**
	 * 发送多条图文消息
	 * @param string $to   		接收方账号OpenID
	 * @param array $msgList  	图文消息数组，key包括title、desc、picurl、url
	 * @param string $from 		开发者微信号OpenID，如果为空，使用类变量
	 * @return boolean  		成功 true，参数为空 false
	 */
	function putMutilImageText($to,array $msgList,$from=NULL){
		if(! is_array($msgList) || !$msgList) return false;
		
		if(! $from ) $from=$this->openId;
		$count=count($msgList);
		if( $count>10 ) $msgList=array_slice($msgList,0,10);
		
        $tpl = '<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%d</CreateTime>
			<MsgType><![CDATA[news]]></MsgType>
			<ArticleCount>%d</ArticleCount><Articles>';
        $r= sprintf($tpl, $to, $from, time(),$count);
        
        $tpl = '<item>
				<Title><![CDATA[%s]]></Title>
				<Description><![CDATA[%s]]></Description>
				<PicUrl><![CDATA[%s]]></PicUrl>
				<Url><![CDATA[%s]]></Url>
				</item>';       	
        foreach ($msgList as $msg)
        	$r .= sprintf($tpl, $msg['title'], $msg['desc'], $msg['picurl'],$msg['url']);
        
		$r .='</Articles></xml>'; 
		echo $r;  
		return true;
	}
	/**
	 * 发送客服文本消息
	 * @param string $text  	文本消息
	 * @param string|NULL $to  	接收方账号OpenID，如果是NULL，取当前用户OpenID
	 * @return bool   			true：成功，false：失败
	 */	
	function sendText($text,$to=NULL){
		$u='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s';
		$d='{"touser":"%s","msgtype":"text","text":{"content":"%s"}}';
		
		if(! $to){
			$c=$t=$token='';
			if($this->getToOpenId($c,$t,$token)) $to=$t;
		}
		if($to){
			$r=$this->getWx(sprintf($u,$this->getAccessToken()),sprintf($d,$to,str_replace('"','\"',$text)) );
			if($r) return true;
		}
		global $context;
		$context->log_error("WeChat sendText error：canot find ToOpenId");
		return false;
	}
	/**
	 * 发送单条客服图文消息
	 * @param string $title		标题
	 * @param string $desc		摘要
	 * @param string $picurl	图片URL
	 * @param string $url		消息URL，点击图文消息即跳转到此URL
	 * @param string $to  		接收方账号OpenID，如果是NULL，取当前用户OpenID
	 * @return boolean  		成功 true，参数为空 false
	 */
	function sendImageText($title,$desc,$picurl,$url,$to=NULL){
		$m=array(array('title'=>$title,'desc'=>$desc,'picurl'=>$picurl,'url'=>$url));
		return $this->sendMutilImageText($m,$to);		
	}	
	/**
	 * 发送多条客服图文消息
	 * @param array  $msgList	图文消息数组，key包括title、desc、picurl、url
	 * @param string $to  		接收方账号OpenID，如果是NULL，取当前用户OpenID
	 * @return bool   			true：成功，false：失败
	 */
	function sendMutilImageText(array $msgList,$to=NULL){
		if(! is_array($msgList) || !$msgList) return false;
		$count=count($msgList);
		if( $count>10 ) $msgList=array_slice($msgList,0,10);
		if(! $to){
			$c=$t=$token='';
			if($this->getToOpenId($c,$t,$token)) $to=$t;
		}
		if(! $to) return false;
		
		$u='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s';
        $tpl = '{"touser":"%s","msgtype":"news","news":{
        	"articles": [';
        $r= sprintf($tpl, $to);
        $tpl='{
             "title":"%s",
             "description":"%s",
             "url":"%s",
             "picurl":"%s"
         	 }';
        $first=true;
		foreach ($msgList as $msg){
			$k=sprintf($tpl, str_replace('"','\"',$msg['title']), str_replace('"','\"',$msg['desc']),$msg['url'], $msg['picurl']);
			if($first) $r .=$k;
			else{
				$r .=','.$k;
				$first=false;
			}
		}
        $r .=']}}';
		if($this->getWx(sprintf($u,$this->getAccessToken()),$r))
			return true;
		return false;
	}
	private function getWx($url,$data=NULL){
		require_lib('net/HttpEx',false);
		$h=new HttpEx($url);
		
		if($data) $r=$h->post($data);	
		else  $r=$h->get();	
		
		if($r===false)	return false;
		
		global $context;
		$r=json_decode($r,true);
		if(isset($r['errcode'])){
			$context->log_error("WeChat wx error ,{$r['errcode']}:{$r['errmsg']}");
			return false;			
		}
		return $r;		
	}
	/**
	 * 得到用户信息 ，详见微信文档
	 * @param string $userOpenId 用户账号OpenID
	 * @return array 用户信息  字段包括：openid ， nickname，sex，city，country，province  
	 */
	function getUserInfo($userOpenId){
		return $this->getWx( sprintf(WECHAT_URL_PATH.'user/info?access_token=%s&openid=%s',
			$this->getAccessToken(),$userOpenId) );
	}
	/**
	 * 得到微信访问凭证access_token
	 */
	function getAccessToken(){
		if($this->accessToken) return $this->accessToken;
		
		global $context;
		$token=$context->cache->get(WECHAT_CACHE_PATH.'access_token');
		$this->accessToken=$token;
		if($token) return $token; 
		
		$token=$this->getWx(sprintf(WECHAT_TOKEN_URL,$this->appId,$this->appSecret));
		
		$this->accessToken=$token['access_token'];
		$context->cache->set(WECHAT_CACHE_PATH.'access_token',$this->accessToken,7000);
		return $this->accessToken;
	}	
	/**
	 * 得到微信sceneId对应的ticket
	 * @param $sceneId  场景ID，永久ticket：1<$sceneId<1000，临时ticket：32位整数
	 * @param $expireSec  有效期（秒数），如果为0，永久ticket
	 * @return array|false false：失败，成功，返回数组，
	 * <br>key包括ticket：二维码ticket，凭借此ticket可以在有效时间内换取二维码 ，
	 * <br>expire_seconds：有效时间，以秒为单位。最大不超过1800
	 */
	function getQrcode($sceneId,$expireSec=0){
		$sceneId =$sceneId <1 ? 1 : ($sceneId>1000 ? 1000 : $sceneId);
		$u='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
		if($expireSec<1)
			$d=sprintf('{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": %d}}}',$sceneId);
		else 
			$d=sprintf('{"expire_seconds": %d, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}',$expireSec,$sceneId);
		return $this->getWx($u,$d);
	}
	/**
	 * 得到$ticket对应的二维码
	 * @param string $ticket getQrcode结果数组中ticket
	 */
	function getQrcodeUrl($ticket){
		return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $ticket;
	}
	/**
	 * 发送图片消息
	 * @param string $to   接收方账号OpenID
	 * @param string $mediaId  通过上传多媒体文件，得到的id
	 * @param string $from 开发者微信号OpenID，如果为空，使用类变量
	 * @return boolean  成功 true，参数为空 false
	 */
	function putImage($to,$mediaId,$from=NULL){
		return $this->putContent($to,$from,1,$mediaId);
	}
	/**
	 * 发送语音消息
	 * @param string $to   接收方账号OpenID
	 * @param string $mediaId  通过上传多媒体文件，得到的id
	 * @param string $from 开发者微信号OpenID，如果为空，使用类变量
	 * @return boolean  成功 true，参数为空 false
	 */
	function putVoice($to,$mediaId,$from=NULL){
		return $this->putContent($to,$from,2,$mediaId);
	}	
	/**
	 * 发送视频消息
	 * @param string $to   接收方账号OpenID
	 * @param string $mediaId  通过上传多媒体文件，得到的id
	 * @param string $thumbMediaId  缩略图的媒体id，通过上传多媒体文件，得到的id
	 * @param string $from 开发者微信号OpenID，如果为空，使用类变量
	 * @return boolean  成功 true，参数为空 false
	 */
	function putVideo($to,$mediaId,$thumbMediaId,$from=NULL){
		return $this->putContent($to,$from,3,$mediaId,$thumbMediaId);
	}	
		
	private function putContent($to,$from,$type,$content,$thumbMediaId=NULL){
		if(! $to || !$from || !$content) return false;
		if(! $from ) $from=$this->openId;
        $tpl = '<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[%s]]></MsgType>';
        
	    if($type==0){
        	$msgType='text';
			$tpl .='<Content><![CDATA[%s]]></Content>';
        }elseif($type==1){
        	$msgType='image';
			$tpl .='<Image><MediaId><![CDATA[%s]]></MediaId></Image>';
        }elseif($type==2){
        	$msgType='voice';
			$tpl .='<Voice><MediaId><![CDATA[%s]]></MediaId></Voice>';
        }elseif($type==3){
        	$msgType='video';
			$tpl .='<Video><MediaId><![CDATA[%s]]></MediaId><ThumbMediaId><![CDATA[thumb_media_id]]></ThumbMediaId></Video>';
        }
        
		$tpl .='</xml>'; 
		if($type==3)
			$r= sprintf($tpl, $to, $from, time(), $msgType, $content,$thumbMediaId);
		else
			$r= sprintf($tpl,$to, $from,  time(), $msgType, $content); 
		echo $r;  
		return true;		
	}		
	/**
	 * 得到两个经纬之间具体，采用圆球体计算，计算简单、误差较大
	 * @param double $lat1	 纬度1
	 * @param double $lng1	经度1
	 * @param double $lat2 	纬度2
	 * @param double $lng2 	经度2
	 */
	static function getDistance($lat1, $lng1, $lat2, $lng2){
	   $radLat1 = deg2rad ($lat1);
	   $radLat2 = deg2rad ($lat2);
	   $a = $radLat1 - $radLat2;
	   $b = deg2rad ($lng1) - deg2rad ($lng2);
	
	   $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
	   $s = $s * 6378.137;//地球半径
	   $s = round($s * 1000) / 1000;
	   return $s;
	}	
}