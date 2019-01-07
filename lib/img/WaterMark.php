<?php
require_once 'gdimage.php';
/**
 * 给图片添加水印，包括图片水印和文字水印
 * @author zengjf
 */
class WaterMark{
	/**
	 * @var string 水印文字字体名称
	 */
	public $font_name='Arial';
	/**
	 * @var int 水印文字字体大小
	 */	
	public $font_size=8;
	/**
	 * @var int 水印文字颜色
	 */		
	public $text_color='#000000';
	/**
	 * @var int 水印文字旋转角度
	 */			
	public $text_rotation=0;
	/**
	 * @var string 水印图片透明颜色，说明：1、 int R,G,B 分别传入RGB值；2、string 传入#开始的6个16进制字符串，如#ff0000；
	 * 3、int 传入单个int型颜色值；4、array 传入R,G,B数组；5、传入预定义的颜色英文名称
	 */
	public $transparent='#ffffff';
	/**
	 * @var string 水印图片透明通道值
	 */
	public $alpha=20;	
	/**
	 * 添加水印图片
	 *  @param string $dest_img	已经添加水印的图像文件路径，如果为NULL，直接输出浏览器
	 * @param string $src_img	原图文件路径
	 * @param string $img		水印图像文件路径
	 * @param integer $position 水印图像位置，0:随机，1:顶居左，2:顶居左,3:顶居右,4:中居左,5:居中,6:中居右,7:底居左,8:底居中,9:底居右
	 * @param string $alpha		水印图像alpha透明值，0:完全透明，100:不透明
	 * @return boolean true:成功
	 */
	function mark_image($dest_img,$src_img,$img ,$position = 0) {
		if(!$img  || ! file_exists ( $img )) return false;
		return $this->do_img($src_img,$dest_img,$position,$img);
	}	
	/**
	 * 添加水印文字
	 * @param string $dest_img 	已经添加水印的图像文件路径
	 * @param string $src_img  	原图文件路径
	 * @param string $text 		水印文字
	 * @param integer $position 水印文字位置，0:随机，1:顶居左，2:顶居左,3:顶居右,4:中居左,5:居中,6:中居右,7:底居左,8:底居中,9:底居右
	 * @return boolean true:成功
	 */
	function mark_text($dest_img ,$src_img,$text,$position = 0) {
		if(! $text) return false;
		$f=new GDFont($this->font_size,$this->font_name);
		$f->set_text($text)->set_rotation($this->text_rotation);
		return $this->do_img($src_img,$dest_img,$position,'',$f,$this->text_color);
	}
	/**
	 * 通过mime类型得到图片类型
	 * @param string $mime mime类型
	 * @return string 图片类型
	 */
	private function image_type($mime) {
		switch (substr ($mime, 6 )){
			case 'gif' : return 'gif';
			case 'jpeg':
			case 'jpg' : return 'jpeg';
			case "png" : return 'png';
		}
		return false;
	}
	/**
	 * 工作函数
	 */
	private function do_img($src_img,$dest_img, $position = 0, $water_img = "", $font=NULL,$font_color=0) {
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
				$pos_x = 1;	$pos_y = 1;	break;
			case 2 : //顶居中
				$pos_x = ($s_w - $w) / 2; $pos_y = 0; break;
			case 3 : //顶居右
				$pos_x = $s_w - $w-1; $pos_y = 1;	break;
			case 4 : //中居左
				$pos_x = 1;	$pos_y = ($s_h - $h) / 2;	break;
			case 5 : //居中
				$pos_x = ($s_w - $w) / 2;	$pos_y = ($s_h - $h) / 2;	break;
			case 6 : //中居右
				$pos_x = $s_w - $w-1; $pos_y = ($s_h - $h) / 2;	break;
			case 7 : //底居左
				$pos_x = 1;	$pos_y = $s_h - $h-1;	break;
			case 8 : //底居中
				$pos_x = ($s_w - $w) / 2;	$pos_y = $s_h - $h-1; break;
			case 9 : //底居右
				$pos_x = $s_w - $w-1;	$pos_y = $s_h - $h-1; break;
			default : //随机
				$pos_x = rand ( 1, ($s_w - $w) );	$pos_y = rand ( 1, ($s_h - $h) );	break;
		}
	
		if (($type = $this->image_type ( $mime )) === false)	return false;
		$func = 'imagecreatefrom' . $type;
		$s_img = $func ( $src_img );
			
		imagealphablending ( $s_img, true );//混色
		
		if ($use_img){
			if(($w_type=$this->image_type($w_mime))===false) return false;
			$func ='imagecreatefrom'.$w_type;
			$w_img=$func($water_img);	
			if ('gif' == $w_type || 'png' == $w_type) 
				imagecolortransparent ( $w_img, GDColor::get_gd_color($w_img,$this->transparent) ); //  设置透明色				
			imagecopymerge($s_img, $w_img, $pos_x, $pos_y, 0, 0, $m_w, $m_h ,$this->alpha);
			imagedestroy ( $w_img );
		} 
		else	$font->draw($s_img,$font_color,$pos_x, $pos_y);
		
		$func ='image'.$type;
		$func( $s_img, $dest_img);
		imagedestroy ( $s_img );
		return true;
	}
	

}