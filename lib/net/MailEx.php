<?php
/**
 * 发送电子邮件
 * @author zengjf
 */
class MailEx{
	private $conf;
    
	/**
	 * @var boolean $html   HTML邮件 or 文本邮件
	 */
	public  $html=false;
	/**
	 * @var boolean $receipt   是否需要回执  true 要求回执， false 不用回执
	 */
	public  $receipt=false;	
	/**
	 * @var string $charset   输入邮件编码
	 */
	public  $charset=NULL;	
	/**
	 * 
	 * @param array  $conf 邮件参数    默认为空，使用app_conf的参数，当设置此参数时，优先使用此参数。
	 * mail_host 邮件服务器,mail_port 邮件服务器端口,mail_user 发送人邮件服务器用户名称,mail_pwd 发送人邮件服务器用户密码。
	 */
	function __construct($conf=array()){
		global $context;
		$this->conf['mail_host']=$context->get_app_conf('mail_host');
		$this->conf['mail_port']=$context->get_app_conf('mail_port');
		$this->conf['mail_user']=$context->get_app_conf('mail_user');
		$this->conf['mail_pwd']=$context->get_app_conf('mail_pwd');
		$this->conf['mail_from']=$context->get_app_conf('mail_from');
		$this->charset = $context->get_app_conf('charset');
		
		$this->conf['mail_from_name']=$context->get_app_conf('mail_from_name');
		if($conf){
			if(isset($conf['mail_host'])) $this->conf['mail_host']=$conf['mail_host'];
			if(isset($conf['mail_port'])) $this->conf['mail_port']=$conf['mail_port'];
			if(isset($conf['mail_user'])) $this->conf['mail_user']=$conf['mail_user'];
			if(isset($conf['mail_pwd'])) $this->conf['mail_pwd']=$conf['mail_pwd'];
			if(isset($conf['mail_from'])) $this->conf['mail_from']=$conf['mail_from'];
			if(isset($conf['mail_from_name'])) $this->conf['mail_from_name']=$conf['mail_from_name'];
		}			
	}
	/**
	 * 设置是否HTML邮件 or 文本邮件
	 * @param boolean $html HTML邮件 or 文本邮件
	 * @return $this
	 */
	public function setHtml($html){
		$this->html=$html;
		return $this;
	}
	/**
	 * 设置是否需要回执  
	 * @param boolean $receipt 是否需要回执  true 要求回执， false 不用回执
	 * @return $this
	 */
	public function setReceipt($receipt){
		$this->receipt=$receipt;
		return $this;
	}
	/**
	 * 设置输入邮件编码  
	 * @param string $charset 输入邮件编码
	 * @return $this
	 */
	public function setCharset($charset){
		$this->charset=$charset;
		return $this;
	}			
	/**
	 * 发送简单通知邮件，发送附件可使用phpmailer。
	 * @param string|array to      接收人
	 * <br>如果是HASH数组，多个邮件地址和邮件名称，如notify_mail(array(jhon@sohu.com=>'江湖人'),'擂台','擂台赛');
	 * <br>如果字符串，单个邮件地址，如果普通数组，多个邮件地址，如notify_mail(array(jhon@sohu.com),'擂台','擂台赛');
	 * @param string subject       邮件标题
	 * @param string body          邮件内容
	 * @param boolean html         HTML邮件 or 文本邮件
	 * @return boolean true成功，false失败
	 */
	function notify_mail($to,$subject,$body){
		return $this->send_mail($to,$this->conf['mail_from'],$this->conf['mail_from_name'],$subject,$body);
	}
	/**
	 * 发送邮件简单，发送附件可使用phpmailer。
	 * @param string|array to      	接收人
	 * <br>如果是HASH数组，多个邮件地址和邮件名称，如send_mail(array(jhon@sohu.com=>'江湖人'),'dxha@sohu.com','大虾','擂台','擂台赛');
	 * <br>如果字符串，单个邮件地址，如果普通数组，多个邮件地址，如notify_mail(array(jhon@sohu.com),'dxha@sohu.com','大虾','擂台','擂台赛');
	 * @param string $from       	发送人邮件地址
	 * @param string $from_name    	发送人名称
	 * @param string subject       	邮件标题
	 * @param string body          	邮件内容
	 * @return boolean true成功，false失败
	 */
	function send_mail($to,$from,$from_name,$subject,$body){
		require_lib('net/extra/phpmailer/class.phpmailer',false);
		global $context;
		$host=$this->conf['mail_host'];
		$port=$this->conf['mail_port'];
		$user=$this->conf['mail_user'];
		$pwd=$this->conf['mail_pwd'];
		if(! $from) $from='root@localhost';
		if(! $from_name) $from_name='root';
		$mail_charset='GBK';			
		$mail=new PHPMailer();
		$mail->CharSet = $mail_charset;
		$mail->IsSMTP();	
		if($this->html)	$mail->ContentType = "text/html; charset={$mail_charset}";
		else $mail->ContentType = "text/plain; charset={$mail_charset}";
	
		if($host) $mail->Host=$host;
		if($port) $mail->Port=$port;
		if($user){
			$mail->SMTPAuth = true;
			$mail->Username=$user;
			$mail->Password=$pwd;
		}
		if($this->receipt){
			$mail->ConfirmReadingTo=$from;
		}
		try{
			$mail->SetFrom($from,iconv($this->charset,$mail_charset,$from_name));	
			if(is_array($to)){
				foreach($to as $addr => $name){
					if(is_int($addr)) $mail->AddAddress($name);
					else $mail->AddAddress($addr,iconv($this->charset,$mail_charset,$name));
				}
			}else $mail->AddAddress($to);	
			$mail->Subject=iconv($this->charset,$mail_charset,$subject);
			$mail->Body=iconv($this->charset,$mail_charset,$body);			
			return $mail->Send();
		}catch(Exception $e){
			$context->log_error("Mail Fail:{$subject}.\r\n".$e->getMessage());
		}
		return false;
	}
}


