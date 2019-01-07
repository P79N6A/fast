<?php
require_once 'HttpEx.php';

class SqSms{
	/**
	 * @var string Sms服务器URL；
	 */
	public $smsUrl='http://smsapi.sqzw.com:8080/sms/productSendAction.action';
	public $mmsUrl='http://smsapi.sqzw.com:8080/sms/mmsSendAction.action';
	/**
	 * @var string 产品锁 
	 */
	public $appKey;
	/**
	 * @var string 密钥
	 */
	public $appSecret;
	/**
	 * @var string 子账号编号(密钥)
	 */
	public $subAccount;	
	/**
	 * @var string  短信签名
	 */
	public $sign;	
	/**
	 * @var int 版本 ，2
	 */
	public $version=2;
	/**
	 * @var int 强制重发 ，0 ：不重发 ，1 ：重发，
	 */
	public $repeated=0;
	/**
	 * @var int 是否回复 ，1：回复，0：不回复
	 */
	public $replay=0;
	/**
	 * @var int 发送优先级 ，1-5，3：群发优先级，5：直发
	 */
	public $priority=5;	
	/**
	 * 
	 * @var int 类型 ，0:直发,1:群发
	 */
	public $flag=0;	
	/**
	 * 
	 * @var int 模板发送类型 ，1:是模板发送，2：不是模板发送
	 */
	public $template=2;	
	/**
	 * @var int 定时发送时间戳
	 */
	public $fixedTime=-1;

	/**
	 * 发送短信
	 * @param string|array $phone 手机号码  ，单个号码或号码列表，
	 * 号码列表形式包括1、","分隔字符串，2、号码数组，号码数目必须<200
	 * @param string $content  短信内容
	 * @param string $task_id  任务号，默认为空，即系统自动生成任务号
	 * @return true|array 成功 ，true；失败：array(错误号，错误信息)，如(-1,'参数错误')
	 */
	function sendSms($phone,$content,$task_id=NULL){
		if(! $phone || ! $content) return array(-1,lang('req_err_param'));
		if( ! $this->subAccount || ! $this->sign ||
			!$this->appKey || !$this->appSecret ) return array(-1,lang('req_err_param'));
		if(!$task_id) 	$task_id = time().rand(1,100000) ;
		$flag = $this->fixedTime < time()? 1 : 2 ; //1: common sms,2:fixed time sms
		$phone=$this->getPhone($phone);
		if(! $phone) return array(-1,lang('req_err_param'));
		$params=array();
		$params['app_key']=$this->appKey;
		$params['sms_sub_account_id']=$this->subAccount;
		$params['app_version']=$this->version;
		$params['sms_priority']=$this->priority;
		$params['sms_flag']=$this->flag;
		$params['sms_template']=$this->template;
		$params['sms_isrepeat']=$this->repeated; 
		$params['sms_isreplay']=$this->replay;
		
		if($flag==2)
			$params['sms_timersend_time']=date ( 'YmdHis', $this->fixedTime);
		else $params['sms_timersend_time']='';
		$p=$params;
		$params['sms_phone']= $phone ;
		$params['sms_text']=base64_encode ( urlencode ( $content ) );
		$params['sms_sign']= base64_encode ( urlencode ( $this->sign ) );	
			
		$p['sms_phone']= $phone ;
		$p['sms_text']=$content;
		$p['sms_sign']=$this->sign;
		$params['app_sign'] =$this->getAppSign($p);
		
		$c=new HttpEx($this->smsUrl);
		$r=$c->post($params);
		if($r!==false) {
			$r=json_decode($r,true);
			if(isset($r['success'])) {
				if(! $r['success']) return array($r['code'],$r['msg']);
				else return true;
			}
		}
		return array(-2,lang('req_err_unknown'));
	}
	/**
	 * 发送二维码彩信
	 * @param string|array $phone 手机号码  ，单个号码或号码列表，
	 * 号码列表形式包括1、","分隔字符串，2、号码数组，号码数目必须<200
	 * @param string $content  二维码
	 * @param string $name	彩信主题
	 * @param string $title 彩信标题
	 * @return true|array 成功 ，true；失败：array(错误号，错误信息)，如(-1,'参数错误') 
	 */
	function sendQrCode($phone,$content,$name,$title){
		if( ! $phone || ! $content) return array(-1,lang('req_err_param'));
		if( ! $this->subAccount || ! $this->sign ||
			!$this->appKey || !$this->appSecret ) return array(-1,lang('req_err_param'));
		$flag = $this->fixedTime < time()? 1 : 2 ;
		 
		$phone=$this->getPhone($phone);
		if(! $phone) return array(-1,lang('req_err_param'));
		
		$size=0;
		$content =& $this->getQrCodeMms($content,$title); 

		$params=array();
		$params['app_key']=$this->appKey;
		$params['app_version']=$this->version;
		$params['sub_account_id']=$this->subAccount;
		$params['mms_istemplate']=1;
		$params['mms_send_canal']=1;	
		$params['mms_type']=2;
		if($flag==2)
			$params['mms_sendtime']=date ( 'YmdHis', $this->fixedTime);
		else $params['mms_sendtime']='';
		$params['mms_contents']=$content;
		$params['mms_phone']= $phone ;
		$p=$params;
		
		$params['mms_name']=base64_encode ( urlencode ($name));
		$params['mms_title']= base64_encode ( urlencode ($title));		
		
		$p['mms_name']= $name;
		$p['mms_title']= $title;		
		$params['app_sign'] =$this->getAppSign($p);
		$c=new HttpEx($this->mmsUrl);
		$r=$c->post($params);
		if($r !==false) {
			$r=json_decode($r,true);
			if(isset($r['success'])) {
				if(! $r['success']) return array($r['code'],$r['msg']);
				else return true;
			}
		}
		return array(-2,lang('req_err_unknown'));				
	}
	/**
	 * 得到前200个号码，并转化为字符串
	 * @param string|array $phone
	 */
	private function getPhone($phone){
		if(is_string($phone) && strpos($phone,',')!==false) $phone=explode(',',$phone);
		if(is_array($phone)){
			$phs=array();
			$i=0;
			foreach ($phone as $p )
			 if($this->checkMobile($p) && ++$i<=200)
			 	 $phs[]=$p; 
			 	 
			if($phs)	return implode(',',$phs);
			else return false;
		}
		if(! $this->checkMobile($phone)) return false;
		return $phone;
	}
	/**
	 * 得到数据签名
	 * @param array $params
	 */
	private function getAppSign(array & $params){
		ksort ( $params );
		$sign = $this->appSecret;
		foreach ( $params as $key => $val ) $sign .= $key . $val;
		$sign .= $this->appSecret;
		return  strtoupper(md5( $sign ));
	}	
	/**
	 * 得到二维码彩信压缩包
	 * @param string $content
	 * @param string $title
	 */	
	private function & getQrCodeMms($content,$title){
		global $context;
		$temp=time().rand(1,100000) ;
		$path=ROOT_PATH."{$context->app_name}/cache/qrcode/mms/{$temp}/";
		$smil	= 'smil_file.smil';
		$img    = '1.gif';
		$txt	= '1.txt';
		
		
		
		if(file_exists($path. $smil . '.zip')) unlink($path. $smil . '.zip');
		if(file_exists($path.$img)) unlink($path.$img);
		if(file_exists($path.$txt)) unlink($path.$txt);
		if(file_exists($path. $smil)) unlink($path. $smil);
		
		if(! file_exists($path))	mkdir ( $path, 0777, true );
		
		$title=iconv('utf-8','gbk',$title);
		$smil_content  = '<smil xmlns="http://www.w3.org/2000/SMIL20/CR/Language"><head><layout><root-layout width="208" height="176" /><region id="Image" left="0" top="0" width="128" height="128" fit="hidden" /><region id="Text" left="0" top="50" width="128" height="128" fit="hidden" /></layout></head><body><par dur="50000ms">';
		$smil_content .= '<img src="'.$img.'" region="Image" />';
		if($title)	$smil_content .= '<text src="'.$txt.'" region="Text" />';
		$smil_content .= '</par></body></smil>';
				
		require_lib ( "img/Barcode" );
		$code=new Barcode();
		$code->codeType=Barcode::CodeQR;
		$code->imgType=Barcode::ImgGIF;
		$code->qrSize=10;
		$code->output($content, $path.$img);
		$zip = new ZipArchive();
		$r=$zip->open($path. $smil . '.zip',ZipArchive::CREATE);
		if($r){
			$zip->addFile($path.$img,$img);
			if($title)	$zip->addFromString($txt,$title);
			$zip->addFromString($smil,$smil_content);
			$zip->close();
		}
		else{
			file_put_contents ( $path. $smil , $smil_content );
			if($title)	file_put_contents ( $path. $txt , $title );
			require_lib ( "output/extra/PHPExcel/Shared/PCLZip/pclzip.lib" );
			$zip_file = new PclZip ( $path. $smil . '.zip' );
			$zip_file->create ( $path, PCLZIP_OPT_REMOVE_PATH, $path );
		}
		$r=base64_encode (file_get_contents (  $path. $smil . '.zip' ));
		
		if(file_exists($path. $smil . '.zip')) unlink($path. $smil . '.zip');
		if(file_exists($path.$img)) unlink($path.$img);
		if(file_exists($path.$txt)) unlink($path.$txt);
		if(file_exists($path. $smil)) unlink($path. $smil);
		
		if(file_exists($path)) rmdir($path);
				
		return $r;
	}
	/**
	 * 检查phone是否为有效手机号码
	 * @param string $phone 手机号码
	 */
   static function checkMobile($phone) {
  	$result = preg_match ( "/^((13[0-9])|(15[^4,\\D])|(18[0-9])|(14[0-9]))\\d{8}$/", $phone );
  	return $result;

  }	

}