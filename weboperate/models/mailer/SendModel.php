<?php
require_model('mailer/class/class.phpmailer');
class SendModel extends BaseModel{
	public $from = "bt@baisonmail.com";
	public $subject = "异常通知";
        public $Password = 'BaoTa123';
        public function __construct($from = "",$subject = ""){
		if (!empty($from)) {
			$this->from = $from;
		}
		if (!empty($subject)) {
			$this->subject = $subject;
		}
		
	}
	/*
	function send($to, $message) {
	    if (!empty($message)) {
	        $mail = new PHPMailer();
	        $mail->IsSMTP(); // 启用SMTP
	        $mail->Host = 'smtp.163.com'; //SMTP服务器
	        $mail->SMTPAuth = true; //开启SMTP认证
	        $mail->CharSet = 'UTF-8';
	        $mail->Port = 25;
	        $mail->Username = '1042152434@163.com'; // SMTP用户名
	        $mail->Password = '881222'; // SMTP密码
	
	        $mail->From = $this->from; //发件人地址
	        $mail->FromName = 'eFAST365系统通知'; //发件人
	
	
	        foreach ($to as $to_mail) {
	            $mail->AddAddress($to_mail, ""); //添加收件人
	        }
	
	        $mail->IsHTML(true); // 是否HTML格式邮件
	
	        $mail->Subject = $this->subject; //邮件主题
	        $mail->Body = $message; //邮件内容
	        $mail->AltBody = "邮件不支持html"; //邮件正文不支持HTML的备用显示
	
	        if (!$mail->Send()) {
	            $this->put_error(-1, $mail->ErrorInfo);
	            return false;
	        }
	
	        return true;
	    }
	    return true;
	} */
        function send($to, $message,$subject) {
	    if (!empty($message)) {
	        $mail = new PHPMailer();
	        $mail->IsSMTP(); // 启用SMTP
	     //   $mail->Host = 'smtp.exmail.qq.com'; //SMTP服务器
			$mail->Host = 'smtp.mxhichina.com'; //SMTP服务器
	        $mail->SMTPAuth = true; //开启SMTP认证
	        $mail->CharSet = 'UTF-8';
	        $mail->Port = 25;//465
	        $mail->Username = $this->from; // SMTP用户名
	        $mail->Password = $this->Password; // SMTP密码
	
	        $mail->From = $this->from; //发件人地址
	        $mail->FromName = 'eFAST365系统通知'; //发件人
	
	
	        foreach ($to as $to_mail) {
	            $mail->AddAddress($to_mail, ""); //添加收件人
	        }
	
	        $mail->IsHTML(true); // 是否HTML格式邮件
	
	        $mail->Subject = $subject; //邮件主题
	        $mail->Body = $message; //邮件内容
	        $mail->AltBody = "邮件不支持html"; //邮件正文不支持HTML的备用显示
	
	        if (!$mail->Send()) {
                    return $this ->format_ret(-1,'',$mail->ErrorInfo);
	        }
	
	        return $this->format_ret(1, '');
	    }
	    return $this->format_ret(1, '');
	} 
	
}