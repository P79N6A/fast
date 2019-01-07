<?php
/**
 * 使用SmtpMail发送简单邮件，发送附件可使用phpmailer。
 * @param string to      		接收人邮件地址
 * @param string to_name       接收人姓名
 * @param string subject       邮件标题
 * @param string body           邮件内容
 * @param array  $conf		       邮件参数    默认为空，使用app_conf的参数，当设置此参数时，优先使用此参数。
 * mail_host 邮件服务器,mail_port 邮件服务器端口,mail_from 发送人邮件地址,mail_from_name 发送人名称,
 * mail_user 发送人邮件服务器用户名称,mail_pwd 发送人邮件服务器用户密码。
 * @param boolean html         HTML邮件 or 文本邮件
 * @param boolean $need_receipt 需要回执 true 要求回执， false 不用回执
 * @return boolean true成功，false失败
 */
function smtp_mail_send($to,$to_name,$subject, $body, $html = false,$need_receipt=false,$conf=array()){
	global $context;
	$charset = $context->get_app_conf('charset');
	$smtp_host=$context->get_app_conf('mail_host');
	$smtp_port=$context->get_app_conf('mail_port');
	$from_mail=$context->get_app_conf('mail_from');
	$from_name=$context->get_app_conf('mail_from_name');
	$smtp_user=$context->get_app_conf('mail_user');
	$smtp_pass=$context->get_app_conf('mail_pwd');
	if($conf){
		if(isset($conf['mail_host'])) $smtp_host=$conf['mail_host'];
		if(isset($conf['mail_port'])) $smtp_port=$conf['mail_port'];
		if(isset($conf['mail_from'])) $from_mail=$conf['mail_from'];
		if(isset($conf['mail_from_name'])) $from_name=$conf['mail_from_name'];
		if(isset($conf['mail_user'])) $smtp_user=$conf['mail_user'];
		if(isset($conf['mail_pwd'])) $smtp_pass=$conf['mail_pwd'];
	}	
	$mail_charset='GBK';
		
    if ($mail_charset !== $charset)  {
        $to_name  = iconv($charset, $mail_charset, $to_name);
        $subject  = iconv($charset, $mail_charset, $subject);
        $body   	  = iconv($charset, $mail_charset, $body);
        $from_name= iconv($charset, $mail_charset,$from_name);
    }	
	
	if (! function_exists ( 'fsockopen' ) && function_exists ( 'mail' )) { //使用mail函数发送邮件
		$content_type = $html ? "Content-Type: text/html; charset={$mail_charset}" : "Content-Type: text/plain; charset={$mail_charset}";
		$headers = array ();
		$headers [] = 'From: "' . '=?' . $mail_charset . '?B?' . base64_encode ( $from_name ) . '?=' . '" <' . $from_mail . '>';
		$headers [] = $content_type . '; format=flowed';
		if ($need_receipt)
			$headers [] = 'Disposition-Notification-To: "' . "=?{$mail_charset}?B?{base64_encode($from_name)}?=" . '"' . "<{$from_mail}>";
		
		return @mail ( $to, '=?' . $mail_charset . '?B?' . base64_encode ( $subject ) . '?=', $body, implode ( "\r\n", $headers ) );
	} else { //使用smtp服务发送邮件
		if (! function_exists ( 'fsockopen' ))
			return false;
		
		$content_type = $html ? "Content-Type: text/html; charset={$mail_charset}" : "Content-Type: text/plain; charset={$mail_charset}";
		$body = base64_encode ( $body );
		
		$headers = array ();
		$headers [] = 'Date: ' . gmdate ( 'D, j M Y H:i:s' ) . ' +0000';
		$headers [] = 'To: "' . '=?' . $mail_charset . '?B?' . base64_encode ( $to_name ) . '?=' . '" <' . $to . '>';
		$headers [] = 'From: "' . '=?' . $mail_charset . '?B?' . base64_encode ( $from_name ) . '?=' . '" <' . $from_mail . '>';
		$headers [] = 'Subject: ' . '=?' . $mail_charset . '?B?' . base64_encode ( $subject ) . '?=';
		$headers [] = $content_type . '; format=flowed';
		$headers [] = 'Content-Transfer-Encoding: base64';
		$headers [] = 'Content-Disposition: inline';
		if ($need_receipt)
			$headers [] = 'Disposition-Notification-To: "' . "=?{$mail_charset}?B?{base64_encode($from_name)}?=" . '"' . "<{$from_mail}>";
		require_lang ( 'net', false );
		if (empty ( $smtp_host ) || empty ( $smtp_port )) {
			$context->log_error ( lang ( 'mail_option_invalid' ) );
			return false;
		}
		
		
		$params ['recipients'] = $to;
		$params ['headers'] = $headers;
		$params ['from'] = $from_mail;
		$params ['body'] = $body;
		
		$smtp = new SmtpMail( array ('host' => $smtp_host, 'port' => $smtp_port, 'user' => $smtp_user, 'pass' => $smtp_pass ) );
		
		if ($smtp->connect () && $smtp->send ( $params ))	return true;
		
		$err_msg = $smtp->error_msg ();
		if (empty ( $err_msg ))
			$err = lang ( 'mail_unknown_err' );
		else if (strpos ( $err_msg, 'Failed to connect to server' ) !== false)
			$err = sprintf ( lang ( 'mail_connnect_fail' ), $smtp_host . ':' . $smtp_port );
		else if (strpos ( $err_msg, 'AUTH command failed' ) !== false)
			$err = lang ( 'mail_auth_fail' );
		elseif (strpos ( $err_msg, 'bad sequence of commands' ) !== false)
			$err = lang ( 'mail_send_fail' );
		else
			$err = $err_msg;
		$context->log_error ( $err );
		return false;
	}
}

define('SMTP_STATUS_NOT_CONNECTED', 1, true);
define('SMTP_STATUS_CONNECTED',     2, true);
class SmtpMail
{
    var $connection;
    var $recipients;
    var $headers;
    var $timeout;
    var $errors;
    var $status;
    var $body;
    var $from;
    var $host;
    var $port;
    var $helo;
    var $auth;
    var $user;
    var $pass;

    /**
     *  参数为一个数组
     *  host        SMTP 服务器的主机       默认：localhost
     *  port        SMTP 服务器的端口       默认：25
     *  helo        发送HELO命令的名称      默认：localhost
     *  user        SMTP 服务器的用户名     默认：空值
     *  pass        SMTP 服务器的登录密码   默认：空值
     *  timeout     连接超时的时间          默认：5
     *  @return  bool
     */
    function __construct($params = array())
    {
        if (!defined('CRLF'))
        {
            define('CRLF', "\r\n", true);
        }

        $this->timeout  = 10;
        $this->status   = SMTP_STATUS_NOT_CONNECTED;
        $this->host     = 'localhost';
        $this->port     = 25;
        $this->auth     = false;
        $this->user     = '';
        $this->pass     = '';
        $this->errors   = array();

        foreach ($params AS $key => $value)
        {
            $this->$key = $value;
        }

        $this->helo     = $this->host;

        //  如果没有设置用户名则不验证
        $this->auth = ('' == $this->user) ? false : true;
    }

    function connect($params = array())
    {
        if (!isset($this->status))
        {
            $obj = new smtp($params);

            if ($obj->connect())
            {
                $obj->status = SMTP_STATUS_CONNECTED;
            }

            return $obj;
        }
        else
        {
            if (!empty($GLOBALS['_CFG']['smtp_ssl']))
            {
                $this->host = "ssl://" . $this->host;
            }
            $this->connection = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

            if ($this->connection === false)
            {
                $this->errors[] = 'Access is denied.';

                return false;
            }

            @socket_set_timeout($this->connection, 0, 250000);

            $greeting = $this->get_data();

            if (is_resource($this->connection))
            {
                $this->status = 2;

                return $this->auth ? $this->ehlo() : $this->helo();
            }
            else
            {
                $this->errors[] = 'Failed to connect to server: ' . $errstr;
                return false;
            }
        }
    }

    /**
     * 参数为数组
     * recipients      接收人的数组
     * from            发件人的地址，也将作为回复地址
     * headers         头部信息的数组
     * body            邮件的主体
     */

    function send($params = array())
    {
        foreach ($params AS $key => $value)
        {
            $this->$key = $value;
        }

        if ($this->is_connected())
        {
            //  服务器是否需要验证
            if ($this->auth)
            {
                if (!$this->auth())
                {
                    return false;
                }
            }

            $this->mail($this->from);

            if (is_array($this->recipients))
            {
                foreach ($this->recipients AS $value)
                {
                    $this->rcpt($value);
                }
            }
            else
            {
                $this->rcpt($this->recipients);
            }

            if (!$this->data())
            {
                return false;
            }

            $headers = str_replace(CRLF . '.', CRLF . '..', trim(implode(CRLF, $this->headers)));
            $body    = str_replace(CRLF . '.', CRLF . '..', $this->body);
            $body    = substr($body, 0, 1) == '.' ? '.' . $body : $body;

            $this->send_data($headers);
            $this->send_data('');
            $this->send_data($body);
            $this->send_data('.');

            return (substr($this->get_data(), 0, 3) === '250');
        }
        else
        {
            $this->errors[] = 'Not connected!';

            return false;
        }
    }

    function helo()
    {
        if (is_resource($this->connection)
                AND $this->send_data('HELO ' . $this->helo)
                AND substr($error = $this->get_data(), 0, 3) === '250' )
        {
            return true;
        }
        else
        {
            $this->errors[] = 'HELO command failed, output: ' . trim(substr($error, 3));

            return false;
        }
    }

    function ehlo()
    {
        if (is_resource($this->connection)
                AND $this->send_data('EHLO ' . $this->helo)
                AND substr($error = $this->get_data(), 0, 3) === '250' )
        {
            return true;
        }
        else
        {
            $this->errors[] = 'EHLO command failed, output: ' . trim(substr($error, 3));

            return false;
        }
    }

    function auth()
    {
        if (is_resource($this->connection)
                AND $this->send_data('AUTH LOGIN')
                AND substr($error = $this->get_data(), 0, 3) === '334'
                AND $this->send_data(base64_encode($this->user))            // Send username
                AND substr($error = $this->get_data(),0,3) === '334'
                AND $this->send_data(base64_encode($this->pass))            // Send password
                AND substr($error = $this->get_data(),0,3) === '235' )
        {
            return true;
        }
        else
        {
            $this->errors[] = 'AUTH command failed: ' . trim(substr($error, 3));

            return false;
        }
    }

    function mail($from)
    {
        if ($this->is_connected()
            AND $this->send_data('MAIL FROM:<' . $from . '>')
            AND substr($this->get_data(), 0, 2) === '250' )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function rcpt($to)
    {
        if ($this->is_connected()
            AND $this->send_data('RCPT TO:<' . $to . '>')
            AND substr($error = $this->get_data(), 0, 2) === '25')
        {
            return true;
        }
        else
        {
            $this->errors[] = trim(substr($error, 3));

            return false;
        }
    }

    function data()
    {
        if ($this->is_connected()
            AND $this->send_data('DATA')
            AND substr($error = $this->get_data(), 0, 3) === '354' )
        {
            return true;
        }
        else
        {
            $this->errors[] = trim(substr($error, 3));

            return false;
        }
    }

    function is_connected()
    {
        return (is_resource($this->connection) AND ($this->status === SMTP_STATUS_CONNECTED));
    }

    function send_data($data)
    {
        if (is_resource($this->connection))
        {
            return fwrite($this->connection, $data . CRLF, strlen($data) + 2);
        }
        else
        {
            return false;
        }
    }

    function get_data()
    {
        $return = '';
        $line   = '';

        if (is_resource($this->connection))
        {
            while (strpos($return, CRLF) === false OR $line{3} !== ' ')
            {
                $line    = fgets($this->connection, 512);
                $return .= $line;
            }

            return trim($return);
        }
        else
        {
            return '';
        }
    }

    /**
     * 获得最后一个错误信息
     */
    function error_msg()
    {
        if (!empty($this->errors))
        {
            $len = count($this->errors) - 1;
            return $this->errors[$len];
        }
        else
        {
            return '';
        }
    }
}
