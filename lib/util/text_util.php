<?php
/**
 * 递归方式的对变量中的特殊字符进行转义
 */
function addslashes_deep($value){
    if (empty($value)){
        return $value;
    }else{
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
}

//生成随机字串,可生成校验码, 默认长度4位,0 字母和数字混合,1 数字,-1 字母
function rand_str($len=4,$only_digit=0) {  
    switch($only_digit) {
        case -1:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            break;
        case 1:
            $chars= str_repeat('0123456789',3);
            break;
        default :
            $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'; //rm 0,o
            break;
    }
    if($len>10 )  $chars= $only_digit==0? str_repeat($chars,$len) : str_repeat($chars,5); //位数过长重复字符串一定次数
    $chars   =   str_shuffle($chars);
    return   substr($chars,0,$len);
}
/**
 * 检查字符串是否是UTF8编码，默认取前512个字节判断。
 * @param string $text 需要检查字符串
 * @param size $check_len 字符串检查最大长度，默认512
 * @return boolean 返回检查结果
 */
function is_utf8($text,$check_len=512)  //
{
	if(strlen($text)>$check_len) $text=substr($text,$check_len);	//forbid text too long
	return preg_match('%^(?:
		 [\x09\x0A\x0D\x20-\x7E]            # ASCII
	   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
   )*$%xs', $text);
}

/**
 * 去除空白字符
 * @param $s 字符串
 */
function strip_blank($s) {
	$r = preg_replace('/[\n\r\t]+/', '', s);
	return preg_replace('/\s{2,}/', ' ', $r);
}
/**
 * 去除html中img的tag
 * @param $s 字符串
 */
function strip_img_tag($s) {
	$s = preg_replace('/(<a[^>]*>)(<img[^>]+alt=")([^"]*)("[^>]*>)(<\/a>)/i', '$1$3$5<br />', $s);
	$s = preg_replace('/(<img[^>]+alt=")([^"]*)("[^>]*>)/i', '$2<br />', $s);
	$s = preg_replace('/<img[^>]*>/i', '', $s);
	return $s;
}
/**
 * 去除html中js,css
 * @param $s 字符串
 */
function strip_script_tag($s) {
	return preg_replace('/(<link[^>]+rel="[^"]*stylesheet"[^>]*>|<img[^>]*>|style="[^"]*")|<script[^>]*>.*?<\/script>|<style[^>]*>.*?<\/style>|<!--.*?-->/is', '', $s);
}

/**
 * @deprecated @see zh_text_util.php
 */
function to_en_marks($text){ 
    $mrk_map = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
                 '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
                 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
                 'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
                 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
                 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
                 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
                 'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
                 'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
                 'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
                 'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
                 'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
                 'ｙ' => 'y', 'ｚ' => 'z',
                 '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[',
                 '】' => ']', '〖' => '[', '〗' => ']', '“' => '"', '”' => '"',
                 '‘' => '\'', '’' => '\'', '｛' => '{', '｝' => '}', '《' => '<',
                 '》' => '>',
                 '％' => '%', '＋' => '+', '—' => '-', '－' => '-',
                 '：' => ':', '。' => '.', '、' => '\\', '，' => ',',
                 '；' => ';', '？' => '?', '！' => '!', '……' => '^', '‖' => '|',
                 '｜' => '|', '〃' => '"', '·'=>'`','～'=>'~',
                 '　' => ' ','＄'=>'$','＠'=>'@','＃'=>'#','＾'=>'^','＆'=>'&','＊'=>'*',
                 '＂'=>'"');
    return strtr($text, $mrk_map);
}
/**
 * 得到uuid，根据ip,pid,time.
 */
function uuid() {
		$host_sig = $_SERVER['SERVER_ADDR'];
		if (strpos($host_sig, ':') !== false) {
			if (substr_count($host_sig, '::')) 
				$host_sig = str_replace('::', str_repeat(':0000', 8 - substr_count($host_sig, ':')) . ':', $host_sig);
			$host_sig = explode(':', $host_sig) ;
			$ipv6 = '' ;
			foreach ($host_sig as $id) 	$ipv6 .= str_pad(base_convert($id, 16, 2), 16, 0, STR_PAD_LEFT);
			$host_sig =  base_convert($ipv6, 2, 10);
			if (strlen($host_sig) < 38) $host_sig = null;
			else 	$host_sig = crc32($host_sig);
		} elseif (empty($host_sig)) {
			$host = $_SERVER['SERVER_NAME'];
			if (empty($host)) 	$host = $_SERVER['HTTP_HOST'];
			if (!empty($host)) {
				$ip = gethostbyname($host);
				if ($ip === $host) 	$host_sig = crc32($host);
				else 	$host_sig = ip2long($ip);
			}
		} elseif ($host_sig !== '127.0.0.1') 	$host_sig = ip2long($host_sig);
		else 	$host_sig = null;

		if (empty($host_sig)) $host_sig = crc32(APP_SALT);

		if (function_exists('zend_thread_id')) $pid = zend_thread_id();
		else $pid = getmypid();
		if (!$pid || $pid > 65535) 	$pid = mt_rand(0, 0xfff) | 0x4000;

		list($time_sec, $time_micro) = explode(' ', microtime());
		return sprintf("%08x-%04x-%04x-%02x%02x-%04x%08x", (int)$time_micro, (int)substr($time_sec, 2) & 0xffff,
			mt_rand(0, 0xfff) | 0x4000, mt_rand(0, 0x3f) | 0x80, mt_rand(0, 0xff), $pid, $host_sig	);
}

/**
 * 去除xss字符，防止xss攻击
 * @param string $text 需要处理的文本
 * @return 返回处理后文本
 */
function remove_xss($text) {
   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
   // this prevents some character re-spacing such as <java\0script>
   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
   $text = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $text);

   // straight replacements, the user should never need these since they're normal characters
   // this prevents like <IMG SRC=@avascript:alert('XSS')>
   $search = 'abcdefghijklmnopqrstuvwxyz';
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $search .= '1234567890!@#$%^&*()';
   $search .= '~`";:?+/={}[]-_|\'\\';
   for ($i = 0; $i < strlen($search); $i++) {
      // ;? matches the ;, which is optional
      // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

      // @ @ search for the hex values
      $text = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $text); // with a ;
      // @ @ 0{0,7} matches '0' zero to seven times
      $text = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $text); // with a ;
   }

   // now the only remaining whitespace attacks are \t, \n, and \r
   $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
   $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
   $ra = array_merge($ra1, $ra2);

   $found = true; // keep replacing as long as the previous round replaced something
   while ($found == true) {
      $val_before = $text;
      for ($i = 0; $i < sizeof($ra); $i++) {
         $pattern = '/';
         for ($j = 0; $j < strlen($ra[$i]); $j++) {
            if ($j > 0) {
               $pattern .= '(';
               $pattern .= '(&#[xX]0{0,8}([9ab]);)';
               $pattern .= '|';
               $pattern .= '|(&#0{0,8}([9|10|13]);)';
               $pattern .= ')*';
            }
            $pattern .= $ra[$i][$j];
         }
         $pattern .= '/i';
         $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
         $text = preg_replace($pattern, $replacement, $text); // filter out the hex tags
         if ($val_before == $text) {
            // no replacements were made, so exit the loop
            $found = false;
         }
      }
   }
   return $text;
}

/**
 * 去除多余、有害文本
 * @param string $text 需要处理的文本
 * @param string $tags 需要保留的标签
 * @return 返回处理后文本
 */
function safe_html($text, $tags = null){
	$text	=	trim($text);
	//完全过滤注释
	$text	=	preg_replace('/<!--?.*-->/','',$text);
	//完全过滤动态代码
	$text	=	preg_replace('/<\?|\?'.'>/','',$text);
	//完全过滤js
	$text	=	preg_replace('/<script?.*\/script>/','',$text);

	$text	=	str_replace('[','&#091;',$text);
	$text	=	str_replace(']','&#093;',$text);
	$text	=	str_replace('|','&#124;',$text);
	//过滤换行符
	$text	=	preg_replace('/\r?\n/','',$text);
	//br
	$text	=	preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
	$text	=	preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
	//过滤危险的属性，如：过滤on事件lang js
	while(preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1],$text);
	}
	while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1].$mat[3],$text);
	}
	if(empty($tags)) {
		$tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
	}
	//允许的HTML标签
	$text	=	preg_replace('/<('.$tags.')( [^><\[\]]*)>/i','[\1\2]',$text);
	//过滤多余html
	$text	=	preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i','',$text);
	//过滤合法的html标签
	while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
		$text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
	}
	//转换引号
	while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1].'|'.$mat[3].'|'.$mat[4],$text);
	}
	//过滤错误的单个引号
	while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
		$text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
	}
	//转换其它所有不合法的 < >
	$text	=	str_replace('<','&lt;',$text);
	$text	=	str_replace('>','&gt;',$text);
	$text	=	str_replace('"','&quot;',$text);
	 //反转换
	$text	=	str_replace('[','<',$text);
	$text	=	str_replace(']','>',$text);
	$text	=	str_replace('|','"',$text);
	//过滤多余空格
	$text	=	str_replace('  ',' ',$text);
	return $text;
}
/**
 * ubb转换为对应的html文本
 * @param string $text 需要处理的文本
 * @return 返回处理后文本
 */
function ubb2html($text) {
  $text=trim($text);
  //$text=htmlspecialchars($text);
  $text=preg_replace("/\\t/is","  ",$text);
  $text=preg_replace("/\[h1\](.+?)\[\/h1\]/is","<h1>\\1</h1>",$text);
  $text=preg_replace("/\[h2\](.+?)\[\/h2\]/is","<h2>\\1</h2>",$text);
  $text=preg_replace("/\[h3\](.+?)\[\/h3\]/is","<h3>\\1</h3>",$text);
  $text=preg_replace("/\[h4\](.+?)\[\/h4\]/is","<h4>\\1</h4>",$text);
  $text=preg_replace("/\[h5\](.+?)\[\/h5\]/is","<h5>\\1</h5>",$text);
  $text=preg_replace("/\[h6\](.+?)\[\/h6\]/is","<h6>\\1</h6>",$text);
  $text=preg_replace("/\[separator\]/is","",$text);
  $text=preg_replace("/\[center\](.+?)\[\/center\]/is","<center>\\1</center>",$text);
  $text=preg_replace("/\[url=http:\/\/([^\[]*)\](.+?)\[\/url\]/is","<a href=\"http://\\1\" target=_blank>\\2</a>",$text);
  $text=preg_replace("/\[url=([^\[]*)\](.+?)\[\/url\]/is","<a href=\"http://\\1\" target=_blank>\\2</a>",$text);
  $text=preg_replace("/\[url\]http:\/\/([^\[]*)\[\/url\]/is","<a href=\"http://\\1\" target=_blank>\\1</a>",$text);
  $text=preg_replace("/\[url\]([^\[]*)\[\/url\]/is","<a href=\"\\1\" target=_blank>\\1</a>",$text);
  $text=preg_replace("/\[img\](.+?)\[\/img\]/is","<img src=\\1>",$text);
  $text=preg_replace("/\[color=(.+?)\](.+?)\[\/color\]/is","<font color=\\1>\\2</font>",$text);
  $text=preg_replace("/\[size=(.+?)\](.+?)\[\/size\]/is","<font size=\\1>\\2</font>",$text);
  $text=preg_replace("/\[sup\](.+?)\[\/sup\]/is","<sup>\\1</sup>",$text);
  $text=preg_replace("/\[sub\](.+?)\[\/sub\]/is","<sub>\\1</sub>",$text);
  $text=preg_replace("/\[pre\](.+?)\[\/pre\]/is","<pre>\\1</pre>",$text);
  $text=preg_replace("/\[email\](.+?)\[\/email\]/is","<a href='mailto:\\1'>\\1</a>",$text);
  $text=preg_replace("/\[colorTxt\](.+?)\[\/colorTxt\]/eis","color_txt('\\1')",$text);
  $text=preg_replace("/\[emot\](.+?)\[\/emot\]/eis","emot('\\1')",$text);
  $text=preg_replace("/\[i\](.+?)\[\/i\]/is","<i>\\1</i>",$text);
  $text=preg_replace("/\[u\](.+?)\[\/u\]/is","<u>\\1</u>",$text);
  $text=preg_replace("/\[b\](.+?)\[\/b\]/is","<b>\\1</b>",$text);
  $text=preg_replace("/\[quote\](.+?)\[\/quote\]/is"," <div class='quote'><h5>引用:</h5><blockquote>\\1</blockquote></div>", $text);
  $text=preg_replace("/\[code\](.+?)\[\/code\]/eis","highlight_code('\\1')", $text);
  $text=preg_replace("/\[php\](.+?)\[\/php\]/eis","highlight_code('\\1')", $text);
  $text=preg_replace("/\[sig\](.+?)\[\/sig\]/is","<div class='sign'>\\1</div>", $text);
  $text=preg_replace("/\\n/is","<br/>",$text);
  return $text;
}