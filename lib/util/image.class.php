<?php
/**
 *颜色类
 */
class GDColor {
	public $r;
	public $g;
	public $b;	
	/**
	 * 根据传入参数创建GD颜色
	 * @param resource $img image资源句柄
	 * @param mixed $color  颜色 ，说明：1、 int R,G,B 分别传入RGB值；2、string 传入#开始的6个16进制字符串，如#ff0000；
	 * 3、int 传入单个int型颜色值；4、array 传入R,G,B数组；5、传入预定义的颜色英文名称
	 * @return int 返回创建的GD颜色
	 */
	public static function get_gd_color($img,$color){
		if(func_num_args()>=4){
			$args = func_get_args();
			$c=new GDColor($args[1],$args[2],$args[3]);
		}else $c=new GDColor($color); 
		return $c->get_color($img);
	}
	/**
	 * 根据传入参数创建对象，得到RGB值
	 * <br>1、int R,G,B 分别传入RGB值
	 * <br>2、string 传入#开始的6个16进制字符串，如#ff0000
	 * <br>3、int 传入单个int型颜色值
	 * <br>4、array 传入R,G,B数组
	 * <br>5、传入预定义的颜色英文名称
	 * @param mixed ...
	 */
	public function __construct() {
		$args = func_get_args();
		$argc = func_num_args();
		if ($argc === 1) {
			if (is_string($args[0]) && strlen($args[0]) === 7 && $args[0]{0} === '#') {
				$this->r = intval(substr($args[0], 1, 2), 16);
				$this->g = intval(substr($args[0], 3, 2), 16);
				$this->b = intval(substr($args[0], 5, 2), 16);
			} else if(is_array($args[0]) && count($args[0])>=3){
				$this->r = $args[0][0];
				$this->g = $args[0][1];
				$this->b = $args[0][2];
			}else {
				if (is_string($args[0])) $args[0] = self::get_color_by_name($args[0]);
				$args[0] = intval($args[0]);
				
				$this->r = ($args[0] & 0xff0000) >> 16;
				$this->g = ($args[0] & 0x00ff00) >> 8;
				$this->b = ($args[0] & 0x0000ff);
			}
		}else if ($argc === 3) {
			$this->r = intval($args[0]);
			$this->g = intval($args[1]);
			$this->b = intval($args[2]);
		}else 	$this->r = $this->g = $this->b = 0;
	}


	/**
	 * 为image根据rgb值创建gd颜色
	 * @param resource $img image资源句柄
	 * @return int 返回创建的颜色
	 */
	public function get_color($img) {
		return imagecolorallocate($img, $this->r, $this->g, $this->b);
	}

	private function get_color_by_name($code, $default = 'white') {
		switch(strtolower($code)) {
			case 'black':	return 0x000000;
			case 'maroon':	return 0x800000;
			case 'red':  	return 0xff0000;
			case 'orange':	return 0xffa500;
			case 'yellow':	return 0xffff00;
			case 'olive':	return 0x808000;
			case 'purple':	return 0x800080;
			case 'fuchsia':	return 0xff00ff;
			case 'lime':	return 0x00ff00;
			case 'green':	return 0x008000;
			case 'navy':	return 0x000080;
			case 'blue':	return 0x0000ff;
			case 'aqua':	return 0x00ffff;
			case 'teal':	return 0x008080;
			case 'silver':	return 0xc0c0c0;
			case 'gray':	return 0x808080;
			case 'ltgray':	return 0xc0c0c0;
			case 'dkgray':	return 0x404040;
			default:		return 0xffffff; //'white'
		}
	}
}
/**
 * 字体类
 * @property string $text 文本
 * @property int $rotation 旋转角度，仅支持0,90,180,270
 * @property $bkcolor 背景色 ，@see GDColor类get_gd_color函数
 */
class GDFont {
	private $rect_inc = 0;
	private $path;
	private $size;
	private $rect;
	private $u_x;
	private $u_y;
	private $is_default=true;
	
	private  $rotation = 0;
	private  $bkcolor=NULL;
	private  $text = '';
	
	
	/**
	 * 根据字体大小$size,名称$font_name创建字体对象。 
	 *  <br>如果不传入字体名称，或者在res/font/目录下找不到对应字体文件，将创建字体文件对应的字体对象，否则，创建默认的 latin2 字体对象
	 *  <br>使用中文必须传入字体名称。
	 * @param string $font_name  字体名称
	 * @param int $size 字体大小
	 */
	public function __construct($size,$font_name=NULL) {
		$this->is_default=true;
		if($font_name){
			$font_name=ROOT_PATH . 'res'.DIRECTORY_SEPARATOR.'font'.DIRECTORY_SEPARATOR.$font_name.'.ttf';
			if(file_exists($font_name)) $this->is_default=false;
		}
		if($this->is_default){	
			$this->size = max(0, intval($size));
			$this->set_bkcolor(new GDColor('white'));
		}else{		
			$this->path = $font_name;
			$this->size = max(5, intval($size));
		}
		$this->set_rotation(0);
	}
	function __get($name){
		if(strcmp('text',$name)==0) return $this->text;
		else if(strcmp('rotation',$name)==0) return $this->rotation;
		else if(strcmp('bkcolor',$name)==0) return $this->bkcolor;
	}
	/**
	 * 设置文本
	 * @param string $text 文本
	 */
	public function set_text($text) {
		$this->text = $text;
		$this->rebuild_box();
		return $this;
	}
	/**
	 * 设置旋转角度
	 * @param int 旋转角度，仅支持0,90,180,270
	 */
	public function set_rotation($rotation) {
		$this->rotation = (int)$rotation;
		if ($this->rotation !== 90 && $this->rotation !== 180 && $this->rotation !== 270) 
			$this->rotation = 0;
		$this->rebuild_box();
		return $this;
	}
	/**
	 * 设置背景色，NULL为无背景色，仅对默认字体有效
	 * @param GDColor $bkcolor 背景色，@see GDColor类get_gd_color函数
	 */
	public function set_bkcolor($bkcolor) {	$this->bkcolor = $bkcolor;return $this;}
	/**
	 * 返回文本宽度和高度
	 * @return int[] 0:宽度,1:高度
	 */
	public function get_dimension() {
		if ($this->is_default) {
			$w = imagefontwidth ( $this->size ) * strlen ( $this->text );
			$h = imagefontheight ( $this->size );
			if ($this->rotation === 90 || $this->rotation === 270)	return array ($h, $w );
			else	return array ($w, $h );
		} else {
			$w = 0.0;	$h = 0.0;
			if ($this->rect !== null) {
				$minx = min ( array ($this->rect [0], $this->rect [2], $this->rect [4], $this->rect [6] ) );
				$maxx = max ( array ($this->rect [0], $this->rect [2], $this->rect [4], $this->rect [6] ) );
				$miny = min ( array ($this->rect [1], $this->rect [3], $this->rect [5], $this->rect [7] ) );
				$maxy = max ( array ($this->rect [1], $this->rect [3], $this->rect [5], $this->rect [7] ) );
				$w = $maxx - $minx;	$h = $maxy - $miny;
			}
			if ($this->rotation === 90 || $this->rotation === 270)
					return array ($h + $this->rect_inc, $w );
			else	return array ($w + $this->rect_inc, $h );
		}
	}
	/**
	 * 在image上画出文本
	 * @param resource $img image资源句柄
	 * @param int|string|array $color|GDColor 颜色，@see GDColor类get_gd_color函数
	 * @param int $x x坐标 ，从left 开始
	 * @param int $y y坐标 ，从bottom 开始
	 */
	public function draw($img, $color, $x, $y) {
		if(is_object($color)) $color_id=$color->get_color($img);
		else $color_id = GDColor::get_gd_color ( $img, $color );
		if ($this->is_default) {
			if ($this->rotation !== 0) {
				$w = imagefontwidth ( $this->size ) * strlen ( $this->text );
				$h = imagefontheight ( $this->size );
				$gd = imagecreatetruecolor ( $w, $h );
				if($this->bkcolor!==NULL)
				  imagefilledrectangle ( $gd, 0, 0, $w - 1, $h - 1, GDColor::get_gd_color ( $gd, $this->bkcolor ) );
				imagestring ( $gd, $this->size, 0, 0, $this->text, $color_id );
				$gd = imagerotate ( $gd, $this->rotation, 0 );
				imagecopy ( $img, $gd, $x, $y, 0, 0, imagesx ( $gd ), imagesy ( $gd ) );
			}
			else{
				imagestring ( $img, $this->size, $x, $y, $this->text, $color_id );
			}	
		} else {
			$dimension = $this->get_dimension ();
			if ($this->rotation === 0)
				$y += abs ( min ( $this->rect [5], $this->rect [7] ) );
			elseif ($this->rotation === 90) {
				$x += abs ( min ( $this->rect [5], $this->rect [7] ) );
				$y += $dimension [1];
			} elseif ($this->rotation === 180) {
				$x += $dimension [0];
				$y += abs ( max ( $this->rect [1], $this->rect [3] ) );
			} elseif ($this->rotation === 270)
				$x += abs ( max ( $this->rect [1], $this->rect [3] ) );
			
			imagettftext ( $img, $this->size, $this->rotation, $x, $y, $color_id, $this->path, $this->text );
		}
	}
	private function rebuild_box() {
		if($this->is_default) return;
		$gd = imagecreate(1, 1);
		$this->rect = imagettftext($gd, $this->size, 0, 0, 0, 0, $this->path, $this->text);

		$this->u_x = abs($this->rect[0]);
		$this->u_y = abs($this->rect[1]);

		if ($this->rotation === 90 || $this->rotation === 270) {
			$this->u_x ^= $this->u_y ^= $this->u_x ^= $this->u_y;
		}
	}
}
/**
 * 标签类
 * @property string $text 文本
 * @property int $rotation 旋转角度，仅支持0,90,180,270
 * @property GDColor $bkcolor 背景色，@see GDColor类get_gd_color函数
 * @property int $position  位置 ,其值为POSITION_TOP、POSITION_RIGHT、POSITION_BOTTOM、POSITION_LEFT
 * @property int $alignment 对齐 ,其值为LEFT、TOP、CENTER、RIGHT、BOTTOM
 * @property int $offset 文本偏移
 * @property int $space 文本空白大小
 */
class GDLabel {
	const LEFT 		= 0;
	const TOP 		= 0;
	const CENTER 	= 1;
	const RIGHT 	= 2;
	const BOTTOM 	= 2;
	
	const POSITION_CENTER=4;
	const POSITION_LEFT = 3;
	const POSITION_TOP = 2;
	const POSITION_RIGHT = 1;
	const POSITION_BOTTOM = 0;
	
	private $font;
	private $position;
	private $alignment;
	private $offset;
	private $space;

	/**
	 * 创建标签对象
	 * @param string $text 文本
	 * @param GDFont $font 字体
	 * @param int $position 文本位置,其值为POSITION_TOP、POSITION_RIGHT、POSITION_BOTTOM、POSITION_LEFT
	 * @param int $alignment 文本对齐,其值为LEFT、TOP、CENTER、RIGHT、BOTTOM
	 */
	public function __construct($text = '', GDFont $font = null, $position = self::POSITION_BOTTOM, $alignment = self::CENTER) {
		$font = $font === null ? new GDFont(5) : $font;
		$this->font = clone $font;
		$this->font->set_rotation(0);
		$this->font->set_bkcolor(new GDColor('white'));
		$this->font->set_text($text);
		
		$this->set_position($position);
		$this->set_alignment($alignment);
		$this->set_space(4);
		$this->set_offset(0);
		$this->set_rotation(0);
		$this->set_bkcolor(new GDColor('white'));
	}

	function __get($name){
		if(strcmp('text',$name)==0) return $this->font->text;
		else if(strcmp('position',$name)==0) return $this->position;
		else if(strcmp('alignment',$name)==0) return $this->alignment;
		else if(strcmp('offset',$name)==0) return $this->offset;
		else if(strcmp('space',$name)==0) return $this->space;
		else if(strcmp('rotation',$name)==0) return $this->font->rotation;
		else if(strcmp('bkcolor',$name)==0) return $this->font->bkcolor;
	}
	/**
	 * 设置文本
	 * @param string $text 文本
	 */
	public function set_text($text) {$this->font->set_text($text);return $this;}
	/**
	 * 设置字体
	 * @param $font 字体对象
	 */
	public function set_font($font) {
		if($font){
			$this->font->set_bkcolor($font->bkcolor);
			$this->font->set_rotation($font->rotation);
		}	
		return $this;
	}
	/**
	 * 设置旋转角度
	 * @param int 旋转角度，仅支持0,90,180,270
	 */
	public function set_rotation($rotation) {$this->font->set_rotation($rotation);return $this;}
	/**
	 * 设置背景色
	 * @param mixed $bkcolor 背景色，@see GDColor类get_gd_color函数
	 */
	public function set_bkcolor($bkcolor) {	$this->font->set_bkcolor($bkcolor);	return $this;}
	/**
	 * 设置文本位置
	 * @param int $position 文本位置,其值为POSITION_TOP、POSITION_RIGHT、POSITION_BOTTOM、POSITION_LEFT
	 */
	public function set_position($position) {
		$position = intval($position);
		if ($position < self::POSITION_TOP || $position > self::POSITION_CENTER)
			$position = self::POSITION_BOTTOM;
		$this->position = $position;
		return $this;
	}
	/**
	 * 设置文本对齐方式
	 * @param int $alignment 对齐 ,其值为LEFT、TOP、CENTER、RIGHT、BOTTOM
	 */
	public function set_alignment($alignment) {
		$alignment = intval($alignment);
		if ($alignment < self::LEFT || $alignment > self::RIGHT)	$alignment = self::CENTER;
		$this->alignment = $alignment;
		return $this;
	}
	/**
	 * 设置文本偏移
	 * @param int $offset 文本偏移
	 */
	public function set_offset($offset) {$this->offset = intval($offset);return $this;}
	/**
	 * 设置文本空白大小
	 * @param int $spacing 文本空白大小
	 */
	public function set_space($space) {$this->space = max(0, intval($space));return $this;}

	/**
	 * 返回标签宽度和高度
	 * @return int[] 0:宽度,1:高度
	 */
	public function get_dimension() {
		$w = 0;		$h = 0;
		$dimension = $this->font->get_dimension();
		$w = $dimension[0];		$h = $dimension[1];
		
		if ($this->position === self::POSITION_TOP || $this->position === self::POSITION_BOTTOM) {
			$h += $this->space;		$w += max(0, $this->offset);
		} else{ 	$w += $this->space;		$h += max(0, $this->offset);}
		return array($w, $h);
	}

	/**
	 * 画出文本
	 * @param resource $img gd图像资源句柄
	 * @param int $x1	x1  
	 * @param int $y1	y1   
	 * @param int $x2	x2   
	 * @param int $y2	y2   
	 * @param mixed $color，@see GDColor类get_gd_color函数
	 */
	public function draw($img, $x1, $y1, $x2, $y2,$color=0) {
		$x = 0;		$y = 0;
		$dim = $this->font->get_dimension();
		if($this->position === self::POSITION_CENTER){
			$x = ($x2-$x1 ) / 2 + $x1 - $dim[0] / 2 ;
			$y = ($y2-$y1 ) / 2 + $y1 - $dim[1] / 2 ;
			
		}else if ($this->position === self::POSITION_TOP || $this->position === self::POSITION_BOTTOM) {
			if ($this->position === self::POSITION_BOTTOM) 		$y = $y2 - $this->space - $dim[1];
			elseif ($this->position === self::POSITION_TOP)		$y = $y1 + $this->space;

			if ($this->alignment === self::CENTER) 		$x = ($x2-$x1 ) / 2 + $x1 - $dim[0] / 2 ;
			elseif ($this->alignment === self::LEFT)	$x = $x1 + $this->offset;
			else	$x = $x2 - $this->offset - $dim[0];
		} else {
			if ($this->position === self::POSITION_RIGHT)		$x = $x2 - $this->space - $dim[0];
			elseif ($this->position === self::POSITION_LEFT)	$x = $x1 + $this->space;

			if ($this->alignment === self::CENTER)		$y = ($y2-$y1 ) / 2 + $y1 - $dim[1] / 2 ; 
			elseif ($this->alignment === self::TOP) 	$y = $y1 + $this->offset+ $dim[1]/2;
			else	$y = $y2 - $this->offset- $dim[1];
		}
		$this->font->draw($img, GDColor::get_gd_color($img,$color), $x, $y);
	}
}

