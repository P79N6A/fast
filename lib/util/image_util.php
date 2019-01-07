<?php

require_lib('util/image',false);
/**
 * 得到图像文件信息
 * @param string $file 图像文件路径
 * @return boolean|array 图像文件信息，width:图像宽度、height:图像高度、type:图像类型、mime:mime类型、size:图像字节数、bits:位深度，如果失败返回false。
 */
function image_get_info($file) {
	if(! file_exists($file)) return false;
	$info = getimagesize ( $file );
	if ($info !== false) {
		$type = strtolower ( substr ( image_type_to_extension ( $info [2] ), 1 ) );
		return array ('width' => $info [0], 'height' => $info [1], 'type' => $type, 
				'size' => filesize ( $file ), 'bits'=>isset($info ['bits']) ? $info ['bits']:NULL,'mime' => $info ['mime'] );
	} else 	return false;
}
/**
 * 生成缩略图
 * @param string $img_file 原始图像文件
 * @param string $thumb_file 缩略图文件
 * @param integer $thumb_width 缩略图宽度，默认200像素
 * @param integer $thumb_height 缩略图高度，默认50像素
 * @param array $trans_color 缩略图透明色，默认为白色
 * @return boolean 函数是否成功
 */
function image_thumb($img_file, $thumb_file, $thumb_width = 200, $thumb_height = 50,$quality=95,$trans_color='#ffffff') {
	$info = image_get_info ( $img_file );
	if ($info === false)	return false;
	
	$s_w = $info ['width'];
	$s_h = $info ['height'];
	$type = $info ['type'];
	unset ( $info );
	
	$scale = min ( $thumb_width / $s_w, $thumb_height / $s_h ); 
	if ($scale >= 1) { // 超过原图大小
		$width = $s_w;
		$height = $s_h;
	} else {
		$width = ( int ) ($s_w * $scale);
		$height = ( int ) ($s_h * $scale);
	}
	
	$create_func = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
	$image = $create_func ( $img_file );
	if ($type != 'gif')	$thumb = imagecreatetruecolor ( $width, $height );
	else	$thumb = imagecreate ( $width, $height );
	
	try {	
		
		if ('gif' == $type || 'png' == $type) 
			imagecolortransparent ( $thumb, GDColor::get_gd_color($thumb,$trans_color) ); //  设置透明色
		imagecopyresampled ( $thumb, $image, 0, 0, 0, 0, $width, $height, $s_w, $s_h );
		if ('jpg' == $type || 'jpeg' == $type)	imageinterlace ( $thumb, 1 );
		
		$image_func = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
		if($type == 'jpg' || $type == 'jpeg'){
			$quality = $quality < 0 ? 0 : ($quality > 100 ? 100 : $quality);
			$image_func ( $thumb, $thumb_file,$quality );
		}else if($type == 'png'){
			$quality = $quality < 0 ? 0 : ($quality > 9 ? 9 : $quality);
			$image_func ( $thumb, $thumb_file,$quality );
		} 
		else $image_func ( $thumb, $thumb_file);
		imagedestroy ( $thumb );
		imagedestroy ( $image );
		return true;
	} catch ( Exception $e ) {
		imagedestroy ( $thumb );
		imagedestroy ( $image );
	}
	return false;
}
/**
 * 请不要使用此函数，_image_water_mark_img_and_txt的子函数
 */
function _image_water_mark_type($mime) {
	switch (substr ($mime, 6 )){
		case 'gif' : return 'gif';
		case 'jpeg':
		case 'jpg' : return 'jpeg';
		case "png" : return 'png';
	}
	return false;
}
/**
 * 请不要使用此函数，使用image_water_mark
 */
function _image_water_mark_img_and_text($src_img,$dest_img, $position = 0, $water_img = "",$alpha=20, $font=NULL,$font_color=0) {
	if (! $src_img  || !file_exists ( $src_img )) return false;
	
	$use_img =$water_img  && file_exists ( $water_img );
	if($use_img) {
		$w_info = getimagesize ( $water_img );
		$m_w = $w_info [0];$m_h = $w_info [1];$w_mime=$w_info ['mime'];
		unset($w_info);
		$w = $m_w;
		$h = $m_h;
	} else {
		$s=$font->get_dimension();
		$w=$s[0];$h=$s[1];
		unset($s);
	}
	
	$info = getimagesize ( $src_img );
	$s_w = $info [0];	$s_h = $info [1];	$mime = $info ["mime"];
	unset ( $info );	
	if (($s_w < $w) || ($s_h < $h)) return false; //图片的长度或宽度比水印还小
	switch ($position) {
		case 1 : //顶居左
			$pos_x = 0;	$pos_y = 0;	break;
		case 2 : //顶居中
			$pos_x = ($s_w - $w) / 2; $pos_y = 0; break;
		case 3 : //顶居右
			$pos_x = $s_w - $w; $pos_y = 0;	break;
		case 4 : //中居左
			$pos_x = 0;	$pos_y = ($s_h - $h) / 2;	break;
		case 5 : //居中
			$pos_x = ($s_w - $w) / 2;	$pos_y = ($s_h - $h) / 2;	break;
		case 6 : //中居右
			$pos_x = $s_w - $w; $pos_y = ($s_h - $h) / 2;	break;
		case 7 : //底居左
			$pos_x = 0;	$pos_y = $s_h - $h;	break;
		case 8 : //底居中
			$pos_x = ($s_w - $w) / 2;	$pos_y = $s_h - $h; break;
		case 9 : //底居右
			$pos_x = $s_w - $w;	$pos_y = $s_h - $h; break;
		default : //随机
			$pos_x = rand ( 0, ($s_w - $w) );	$pos_y = rand ( 0, ($s_h - $h) );	break;
	}

	if (($type = _image_water_mark_type ( $mime )) === false)	return false;
	$func = 'imagecreatefrom' . $type;
	$s_img = $func ( $src_img );
		
	imagealphablending ( $s_img, true );//混色
	
	if ($use_img){
		if(($w_type=_image_water_mark_type($w_mime))===false) return false;
		$func ='imagecreatefrom'.$w_type;
		$w_img=$func($water_img);		
		imagecopymerge($s_img, $w_img, $pos_x, $pos_y, 0, 0, $m_w, $m_h ,$alpha);
		imagedestroy ( $w_img );
	} 
	else	$font->draw($s_img,$font_color,$pos_x, $pos_y);
	
	$func ='image'.$type;
	$func( $s_img, $dest_img);
	imagedestroy ( $s_img );
	return true;
}
/**
 * 水印
 * @param string $src_img  原始图像文件路径
 * @param string $dest_img 加水印后图像文件路径
 * @param string $water_img 水印图像文件路径
 * @param integer $position 水印位置，0:随机，1:顶居左，2:顶居左,3:顶居右,4:中居左,5:居中,6:中居右,7:底居左,8:底居中,9:底居右
 * @param string $alpha		水印图像alpha透明值，0:完全透明，100:不透明
 * @return 成功true
 */
function image_water_mark($src_img,$dest_img,$water_img ,$position = 0,$alpha=20) {
	if(!$water_img  || ! file_exists ( $water_img )) return false;
	return _image_water_mark_img_and_text($src_img,$dest_img,$position,$water_img,$alpha);
}
/**
 * 文字水印，请谨慎使用此函数
 * @param string $src_img  原始图像文件路径
 * @param string $dest_img 加水印后图像文件路径
 * @param string $text 水印文字
 * @param array|string $font_color 文字颜色 
 * @param integer $position 水印位置，0:随机，1:顶居左，2:顶居左,3:顶居右,4:中居左,5:居中,6:中居右,7:底居左,8:底居中,9:底居右
 * @return 成功true
 */
function image_water_mark_text($src_img,$dest_img ,$text,$font_name=NULL,$font_size=5,$color = '#000000',$rotation=0,$position = 0) {
	if(! $text) return false;
	$f=new GDFont($font_size,$font_name);
	$f->set_text($text)->set_rotation($rotation);
	return _image_water_mark_img_and_text($src_img,$dest_img,$position,'',0,$f,$color);
}
/**
 * 输出校验码图像到web，例:	image_output_verify(strtoupper(rand_str(4)));
 * @param string  $text			校验码，一般为4位大写，参见lib/util/text_util.php,rand_str函数
 * @param integer $chaos_mode	混淆模式，0:无，1:点，2:线，4:全部
 * @param integer $width		图像宽度，默认50
 * @param integer $height		图像高度，默认22
 * @param boolean $is_png		是否为png图像，否则为gif图像，默认true
 */
function image_output_verify($text, $chaos_mode=1,$width = 62, $height = 24, $is_png=true) {
	$length = strlen ( $text );
	$width = ($length * 10 + 10) > $width ? $length * 10 + 10 : $width;
	if ($is_png) $img = @imagecreatetruecolor ( $width, $height );
	else $img = @imagecreate ( $width, $height );
	
	$r = Array (225, 255, 255, 223 );
	$g = Array (225, 236, 237, 255 );
	$b = Array (225, 236, 166, 125 );
	$key = mt_rand ( 0, 3 );

	@imagefilledrectangle ( $img, 0, 0, $width - 1, $height - 1, GDColor::get_gd_color($img,$r [$key], $g [$key], $b [$key] ) );
	$point_color = GDColor::get_gd_color( $img, mt_rand ( 0, 255 ), mt_rand ( 0, 255 ), mt_rand ( 0, 255 ) ); 
	if(2===$chaos_mode || 4===$chaos_mode)
	for($i = 0; $i < 10; $i ++) {
		$point_color = GDColor::get_gd_color ( $img, mt_rand ( 100, 255 ), mt_rand ( 100, 255 ), mt_rand ( 100, 255 ) );
		imagearc ( $img, mt_rand ( - 10, $width ), mt_rand ( - 10, $height ), mt_rand ( 30, 300 ), mt_rand ( 20, 200 ), 55, 44, $point_color );
	}
	if(1===$chaos_mode || 4===$chaos_mode)
	for($i = 0; $i < 80; $i ++) {
		$point_color = GDColor::get_gd_color ( $img, mt_rand ( 50, 255 ), mt_rand ( 50, 255 ), mt_rand ( 50, 255 ) );
		imagesetpixel ( $img, mt_rand ( 0, $width ), mt_rand ( 0, $height ), $point_color );
	}
	$text_color = array( mt_rand ( 0, 120 ), mt_rand ( 0,80 ), mt_rand ( 0, 80 ) );
	for($i = 0; $i < $length; $i ++) {
		$f=new GDFont(14,'Arial'); 
		$f->set_text($text[$i])->draw($img,$text_color,$i * 15 + 2, mt_rand ( 2,8 ));
	}
	@imagerectangle ( $img, 0, 0, $width - 1, $height - 1,  GDColor::get_gd_color( $img, '#808080') );
	if($is_png){
		header ( "Content-type: image/png");
		imagepng($img);
	}else{
		header ( "Content-type: image/gif");
		imagegif($img);		
	}
	imagedestroy ( $img );
	exit;
} 
/**
 * 生成UPCA条形码，如果条形码$text>12位，将取前12位。
 * @param string $text		条形码数字串，>12位，将取前12位。
 * @param integer $bar_height 条形码高度，默认100像素
 * @param integer $bar_width  条形码宽度，默认2
 * @param integer $font_size 标签文字大小，默认11
 * @param string $font_name  标签字体，默认Arial
 * @param string $file 条形码文件保存路径，如果为NULL，导出为web的image/png，默认NULL
 * @return boolean true成功，false失败
 */
function barcode_upca($text,$bar_height=100,$bar_width=2,$font_size=11,$font_name='Arial',$file=NULL) 
{ 
    /*code params*/
    $lencode = array('0001101','0011001','0010011','0111101','0100011', 
                    '0110001','0101111','0111011','0110111','0001011'); 
    $rencode = array('1110010','1100110','1101100','1000010','1011100', 
                    '1001110','1010000','1000100','1001000','1110100'); 
    $ends = '101'; $center = '01010'; 
	    
    if ( strlen($text) < 11 ) return false; //upca:11 digits,12->checksum
    else $text=substr($text, 0, 11);		//if >11,use head 11 digits
    
 	/*compute checksum*/
    $tmp = '0'.$text; 
    $even = 0; $odd = 0; 
    for ($i=0;$i<12;$i++) 
        if ($i % 2) $odd += $tmp[$i];
        else  $even += $tmp[$i];  
    $text.=(10 - (($odd * 3 + $even) % 10)) % 10; 
 
    /*encode text*/ 
    $bars=$ends; 
    $bars.=$lencode[$text[0]]; 
    for($i=1;$i<6;$i++) $bars.=$lencode[$text[$i]]; 
    $bars.=$center; 
    for($i=6;$i<12;$i++) $bars.=$rencode[$text[$i]]; 
    $bars.=$ends; 
    
    $font=new GDFont($font_size,$font_name);
    $font->set_text($text[0]);
    $sz=$font->get_dimension();
 	$hspace=$sz[1]-2;
 	$wspace=$sz[0]+4;
 	
    /* create image */ 
    $w=$bar_width*95+$wspace*2;$h=$bar_height+$sz[1]*2;
    $img = imagecreate($w,$h);
	try {
		$fg = GDColor::get_gd_color ( $img, 0, 0, 0 );
		$bg = GDColor::get_gd_color ( $img, 255, 255, 255 );
		imagefilledrectangle ( $img, 0, 0, $w, $h, $bg );
		
		for($i = 0; $i < strlen ( $bars ); $i ++) {
			if (($i < 10) || ($i >= 45 && $i < 50) || ($i >= 85))	$sh = $sz [1];
			else	$sh = 0;
			if ($bars [$i] == '1')	$color = $fg;
			else	$color = $bg;
			imagefilledrectangle ( $img, ($i * $bar_width) + $wspace, 5, ($i + 1) * $bar_width + $wspace - 1, $bar_height + 5 + $sh, $color ); //
		}
		
		/* add label*/
		$font->set_text ( $text [0] );
		$font->draw ( $img, $fg, 2, $bar_height - $hspace );
		for($i = 0; $i < 5; $i ++) {
			$font->set_text ( $text [$i + 1] );
			$font->draw ( $img, $fg, $bar_width * (13 + $i * 6) + $wspace, $bar_height + $hspace );
			$font->set_text ( $text [$i + 6] );
			$font->draw ( $img, $fg, $bar_width * (53 + $i * 6) + $wspace, $bar_height + $hspace );
		}
		$font->set_text ( $text [11] );
		$font->draw ( $img, $fg, $w - $sz [0] - 2, $bar_height - $hspace ); //$lw*95+17
		
		/*output*/
		if ($file === NULL) {
			header ( "Content-Type: image/png" );
			imagepng ( $img );
		} else {
			ob_start ();
			imagepng ( $img );
			$bin = ob_get_contents ();
			ob_end_clean ();
			@file_put_contents ( $this->filename, $bin );
		}
		@imagedestroy ( $img );
		return true;
	} catch ( Exception $e ) {
		@imagedestroy ( $img );
		return false;
	}
 	
} 
/**
 * 生成EAN13条形码，如果条形码$text>13个，将取前13位。
 * @param string $text		条形码数字串，>13个，将取前13位。
 * @param integer $bar_height 条形码高度，默认100像素
 * @param integer $bar_width  条形码宽度，默认2
 * @param integer $pad		   填充空白，默认5
 * @param integer $font_size 标签文字大小，默认11
 * @param string $font_name  标签字体，默认Arial
 * @param string $file 条形码文件保存路径，如果为NULL，导出为web的image/png，默认NULL
 * @return boolean true成功，false失败
 */
function barcode_ean13($text,$bar_height=100,$bar_width=2,$pad=5,$font_size=11,$font_name='Arial',$file=NULL) {
		/*ean13 params*/
	$guide = array (1 => 'AAAAAA', 'AABABB', 'AABBAB', 'ABAABB', 'ABBAAB', 'ABBBAA', 'ABABAB', 'ABABBA', 'ABBABA' );
	$lstart = '101';
	$lencode = array ("A" => array ('0001101', '0011001', '0010011', '0111101', '0100011', '0110001', '0101111', '0111011', '0110111', '0001011' ), "B" => array ('0100111', '0110011', '0011011', '0100001', '0011101', '0111001', '0000101', '0010001', '0001001', '0010111' ) );
	$rencode = array ('1110010', '1100110', '1101100', '1000010', '1011100', '1001110', '1010000', '1000100', '1001000', '1110100' );
	$center = '01010';
	$ends = '101';
	if (strlen ( $text ) < 12)		return false; //ean13:12 digits,13->checksum
	

	if (strlen ( $text ) == 12)		$text .= '0';
	else if (strlen ( $text ) >= 13)
		$text = substr ( $text, 0, 13 ); //if >12,use head 12 digits  
	

	$lsum = 0;
	$rsum = 0;
	for($i = 0; $i < (strlen ( $text ) - 1); $i ++)
		if ($i % 2)			$lsum += ( int ) $text [$i];
		else			$rsum += ( int ) $text [$i];
	
	$tsum = $lsum * 3 + $rsum;
	if ($text [12] != (10 - ($tsum % 10)))		$text [12] = 10 - ($tsum % 10);
	
	$barcode = $lstart;
	for($i = 1; $i <= 6; $i ++)	$barcode .= $lencode [$guide [$text [0]] [($i - 1)]] [$text [$i]];
	$barcode .= $center;
	for($i = 7; $i < 13; $i ++)	$barcode .= $rencode [$text [($i)]];
	$barcode .= $ends;
	
	$font = new GDFont ( $font_size, $font_name );
	$font->set_text ( $text [0] );
	$sz = $font->get_dimension ();
	$hspace = $sz [1] - 2;
	$w_offset = $sz [0] + $pad;
	$w = $bar_width * 95 + $w_offset * 2;
	$h = $bar_height + $sz [1] * 2 + $pad;
	
	$img = imagecreate ( $w, $h );
	try {
		$fg = GDColor::get_gd_color ( $img, 0, 0, 0 );
		$bg = GDColor::get_gd_color ( $img, 255, 255, 255 );
		imagefilledrectangle ( $img, 0, 0, $w, $h, $bg );
		for($i = 0; $i < strlen ( $barcode ); $i ++) {
			if (($i < 4) || ($i >= 45 && $i < 50) || ($i >= 92))	$sh = $sz [1]; 
			else	$sh = 0;
			if ($barcode [$i] == '1')	$color = $fg;
			else	$color = $bg;
			imagefilledrectangle ( $img, ($i * $bar_width) + $w_offset, $pad, ($i + 1) * $bar_width + $w_offset - 1, $bar_height + $pad + $sh, $color );
		}
		$font->set_text ( $text [0] );
		$font->draw ( $img, $fg, $w_offset - $sz [1], $bar_height + $hspace );
		for($i = 0; $i < 6; $i ++) {
			$font->set_text ( $text [$i + 1] );
			$font->draw ( $img, $fg, $bar_width * (8 + $i * 6) + $w_offset, $bar_height + $hspace );
			$font->set_text ( $text [$i + 7] );
			$font->draw ( $img, $fg, $bar_width * (53 + $i * 6) + $w_offset, $bar_height + $hspace );
		}
		/*output*/
		if ($file === NULL) {
			header ( "Content-Type: image/png" );
			imagepng ( $img );
		} else {
			ob_start ();
			imagepng ( $img );
			$bin = ob_get_contents ();
			ob_end_clean ();
			@file_put_contents ( $this->filename, $bin );
		}
		@imagedestroy ( $img );
		return true;
	} catch ( Exception $e ) {
		@imagedestroy ( $img );
		return false;
	}
	  
}
