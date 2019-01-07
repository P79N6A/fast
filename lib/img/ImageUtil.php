<?php
require_once 'gdimage.php';
/**
 * 图像实用类，包括缩略图、校验码
 * @author zengjf
 */
class ImageUtil{
	/**
	 * @var int 缩略图质量0~100
	 */
	public $thumb_quality=95;
	/**
	 * @var 校验码混淆模式   0:无，1:点，2:线，4:全部  
	 */
	public $verify_mode=4;
	/**
	 * 得到图像文件信息
	 * @param string $file 图像文件路径
	 * @return boolean|array 图像文件信息，width:图像宽度、height:图像高度、type:图像类型、mime:mime类型、size:图像字节数、bits:位深度，如果失败返回false。
	 */
	static function get_info($file) {
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
	 * @param string $thumb_file 缩略图文件，如果为NULL，输出到浏览器。
	 * @param integer $thumb_width 缩略图宽度，默认200像素
	 * @param integer $thumb_height 缩略图高度，默认50像素
	 * @param array $transparent 缩略图透明色，默认为白色， 说明：1、 int R,G,B 分别传入RGB值；2、string 传入#开始的6个16进制字符串，如#ff0000；
	 * 3、int 传入单个int型颜色值；4、array 传入R,G,B数组；5、传入预定义的颜色英文名称
	 * @return boolean 函数是否成功
	 */
	function thumb($img_file, $thumb_file=NULL, $thumb_width = 200, $thumb_height = 50,$transparent='#ffffff') {
		$info = self::get_info ( $img_file );
		if ($info === false)	return false;
		
		$s_w = $info ['width'];
		$s_h = $info ['height'];
		$type = $info ['type'];
		$mime = $info ['mime'];
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
		
		if(! $thumb_file)	header ( "Content-type: {$mime}");
		try {	
			
			if ('gif' == $type || 'png' == $type) 
				imagecolortransparent ( $thumb, GDColor::get_gd_color($thumb,$transparent) ); //  设置透明色
			imagecopyresampled ( $thumb, $image, 0, 0, 0, 0, $width, $height, $s_w, $s_h );
			if ('jpg' == $type || 'jpeg' == $type)	imageinterlace ( $thumb, 1 );
			
			$image_func = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
			if($type == 'jpg' || $type == 'jpeg'){
				$this->thumb_quality = $this->thumb_quality < 0 ? 0 :
					 ($this->thumb_quality > 100 ? 100 : $this->thumb_quality);
				$image_func ( $thumb, $thumb_file,$this->thumb_quality );
			}else if($type == 'png'){
				$this->thumb_quality = $this->thumb_quality < 0 ? 0 : 
					($this->thumb_quality > 9 ? 9 : $this->thumb_quality);
				$image_func ( $thumb, $thumb_file,$this->thumb_quality );
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
	 * 输出校验码图像到web，例:	output_verify(62,24,rand_str(4));
	 * @param integer $width		图像宽度，默认62
	 * @param integer $height		图像高度，默认24 
	 * @param string  $text			校验码，一般为4位大写，如果为NULL，采用util/text_util.php,rand_str函数生成4位随机字母数字。
	 */
	function output_verify($width = 62, $height = 24,$text=NULL) {
		if(!$text) {
			require_lib('util/text_util',false);
			$text=rand_str();
		}
		$length = strlen ( $text );
		
		$width = ($length * 10 + 10) > $width ? $length * 10 + 10 : $width;
		$img = @imagecreatetruecolor ( $width, $height );
		
		$r = Array (225, 255, 255, 223 );
		$g = Array (225, 236, 237, 255 );
		$b = Array (225, 236, 166, 125 );
		$key = mt_rand ( 0, 3 );
	
		@imagefilledrectangle ( $img, 0, 0, $width - 1, $height - 1, GDColor::get_gd_color($img,$r [$key], $g [$key], $b [$key] ) );
		$point_color = GDColor::get_gd_color( $img, mt_rand ( 0, 255 ), mt_rand ( 0, 255 ), mt_rand ( 0, 255 ) ); 
		if(2===$this->verify_mode || 4===$this->verify_mode)
		for($i = 0; $i < 10; $i ++) {
			$point_color = GDColor::get_gd_color ( $img, mt_rand ( 100, 255 ), mt_rand ( 100, 255 ), mt_rand ( 100, 255 ) );
			imagearc ( $img, mt_rand ( - 10, $width ), mt_rand ( - 10, $height ), mt_rand ( 30, 300 ), mt_rand ( 20, 200 ), 55, 44, $point_color );
		}
		if(1===$this->verify_mode || 4===$this->verify_mode)
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
		header ( "Content-type: image/png");
		imagepng($img);
		imagedestroy ( $img );
		exit;
	} 
}
