<?php
class BarcodeException extends Exception {}

abstract class BarcodeBase {
	const COLOR_BG = 0;
	const COLOR_FG = 1;

	protected $colorFg, $colorBg;		 
	protected $scale;					 
	protected $offsetX, $offsetY;		 
	protected $labels = array();		 
	protected $pushLabel = array(0, 0); 

	protected function __construct() {
		$this->setOffsetX(0);
		$this->setOffsetY(0);
		$this->setForegroundColor(0x000000);
		$this->setBackgroundColor(0xffffff);
		$this->setScale(1);
	}

	public function parse($text) {
	}

	public function getForegroundColor() {
		return $this->colorFg;
	}

	public function setForegroundColor($code) {
		if ($code instanceof BCColor) {
			$this->colorFg = $code;
		} else {
			$this->colorFg = new BCColor($code);
		}
	}

	public function getBackgroundColor() {
		return $this->colorBg;
	}
	
	public function setBackgroundColor($code) {
		if ($code instanceof BCColor) {
			$this->colorBg = $code;
		} else {
			$this->colorBg = new BCColor($code);
		}

		foreach ($this->labels as $label) {
			$label->setBackgroundColor($this->colorBg);
		}
	}

	public function setColor($fg, $bg) {
		$this->setForegroundColor($fg);
		$this->setBackgroundColor($bg);
	}

	public function getScale() {
		return $this->scale;
	}

	public function setScale($scale) {
		$scale = intval($scale);
		if ($scale <= 0) {
			throw new BarcodeException('The scale must be larger than 0.scale');
		}

		$this->scale = $scale;
	}

	public abstract function draw($im);

	/**
	 * @param int $w
	 * @param int $h
	 * @return int[] [0]->width [1]->height
	 */
	public function getDimension($w, $h) {
		$labels = $this->getBiggestLabels(false);
		$pixelsAround = array(0, 0, 0, 0); // TRBL
		if (isset($labels[BCLabel::POSITION_TOP])) {
			$dimension = $labels[BCLabel::POSITION_TOP]->getDimension();
			$pixelsAround[0] += $dimension[1];
		}

		if (isset($labels[BCLabel::POSITION_RIGHT])) {
			$dimension = $labels[BCLabel::POSITION_RIGHT]->getDimension();
			$pixelsAround[1] += $dimension[0];
		}

		if (isset($labels[BCLabel::POSITION_BOTTOM])) {
			$dimension = $labels[BCLabel::POSITION_BOTTOM]->getDimension();
			$pixelsAround[2] += $dimension[1];
		}

		if (isset($labels[BCLabel::POSITION_LEFT])) {
			$dimension = $labels[BCLabel::POSITION_LEFT]->getDimension();
			$pixelsAround[3] += $dimension[0];
		}

		$finalW = ($w + $this->offsetX) * $this->scale;
		$finalH = ($h + $this->offsetY) * $this->scale;

		$reversedLabels = $this->getBiggestLabels(true);
		foreach ($reversedLabels as $label) {
			$dimension = $label->getDimension();
			$alignment = $label->getAlignment();
			if ($label->getPosition() === BCLabel::POSITION_LEFT || $label->getPosition() === BCLabel::POSITION_RIGHT) {
				if ($alignment === BCLabel::ALIGN_TOP) {
					$pixelsAround[2] = max($pixelsAround[2], $dimension[1] - $finalH);
				} elseif ($alignment === BCLabel::ALIGN_CENTER) {
					$temp = ceil(($dimension[1] - $finalH) / 2);
					$pixelsAround[0] = max($pixelsAround[0], $temp);
					$pixelsAround[2] = max($pixelsAround[2], $temp);
				} elseif ($alignment === BCLabel::ALIGN_BOTTOM) {
					$pixelsAround[0] = max($pixelsAround[0], $dimension[1] - $finalH);
				}
			} else {
				if ($alignment === BCLabel::ALIGN_LEFT) {
					$pixelsAround[1] = max($pixelsAround[1], $dimension[0] - $finalW);
				} elseif ($alignment === BCLabel::ALIGN_CENTER) {
					$temp = ceil(($dimension[0] - $finalW) / 2);
					$pixelsAround[1] = max($pixelsAround[1], $temp);
					$pixelsAround[3] = max($pixelsAround[3], $temp);
				} elseif ($alignment === BCLabel::ALIGN_RIGHT) {
					$pixelsAround[3] = max($pixelsAround[3], $dimension[0] - $finalW);
				}
			}
		}

		$this->pushLabel[0] = $pixelsAround[3];
		$this->pushLabel[1] = $pixelsAround[0];

		$finalW = ($w + $this->offsetX) * $this->scale + $pixelsAround[1] + $pixelsAround[3];
		$finalH = ($h + $this->offsetY) * $this->scale + $pixelsAround[0] + $pixelsAround[2];

		return array($finalW, $finalH);
	}

	public function getOffsetX() {
		return $this->offsetX;
	}

	public function setOffsetX($offsetX) {
		$offsetX = intval($offsetX);
		if ($offsetX < 0) {
			throw new BarcodeException('The offset X must be 0 or larger. offsetX');
		}

		$this->offsetX = $offsetX;
	}

	public function getOffsetY() {
		return $this->offsetY;
	}

	public function setOffsetY($offsetY) {
		$offsetY = intval($offsetY);
		if ($offsetY < 0) {
			throw new BarcodeException('The offset Y must be 0 or larger.offsetY');
		}

		$this->offsetY = $offsetY;
	}

	public function addLabel(BCLabel $label) {
		$label->setBackgroundColor($this->colorBg);
		$this->labels[] = $label;
	}

	public function removeLabel(BCLabel $label) {
		$remove = -1;
		$c = count($this->labels);
		for ($i = 0; $i < $c; $i++) {
			if ($this->labels[$i] === $label) {
				$remove = $i;
				break;
			}
		}

		if ($remove > -1) {
			array_splice($this->labels, $remove, 1);
		}
	}

	public function clearLabels() {
		$this->labels = array();
	}
	public function output($filename=NULL){
		$drawing = new BCDraw();
		$drawing->setDPI(96);
		if($filename) $drawing->setFileName($filename);
		$drawing->draw($this);
	}
	protected function drawText($im, $x1, $y1, $x2, $y2) {
		foreach ($this->labels as $label) {
			$label->draw($im,
				($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0],
				($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1],
				($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0],
				($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1]);
		}
	}

	protected function drawPixel($im, $x, $y, $color = self::COLOR_FG) {
		$xR = ($x + $this->offsetX) * $this->scale + $this->pushLabel[0];
		$yR = ($y + $this->offsetY) * $this->scale + $this->pushLabel[1];

		// We always draw a rectangle
		imagefilledrectangle($im,
			$xR,
			$yR,
			$xR + $this->scale - 1,
			$yR + $this->scale - 1,
			$this->getColor($im, $color));
	}

	protected function drawRectangle($im, $x1, $y1, $x2, $y2, $color = self::COLOR_FG) {
		if ($this->scale === 1) {
			imagerectangle($im,
				($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0],
				($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1],
				($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0],
				($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1],
				$this->getColor($im, $color));
		} else {
			imagefilledrectangle($im, ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
			imagefilledrectangle($im, ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
			imagefilledrectangle($im, ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
			imagefilledrectangle($im, ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
		}
	}

	protected function drawFilledRectangle($im, $x1, $y1, $x2, $y2, $color = self::COLOR_FG) {
		if ($x1 > $x2) { // Swap
			$x1 ^= $x2 ^= $x1 ^= $x2;
		}

		if ($y1 > $y2) { // Swap
			$y1 ^= $y2 ^= $y1 ^= $y2;
		}

		imagefilledrectangle($im,
			($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0],
			($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1],
			($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1,
			($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1,
			$this->getColor($im, $color));
	}

	function getColor($im, $color) {
		if ($color === self::COLOR_BG) {
			return $this->colorBg->allocate($im);
		} else {
			return $this->colorFg->allocate($im);
		}
	}

	private function getBiggestLabels($reversed = false) {
		$searchLR = $reversed ? 1 : 0;
		$searchTB = $reversed ? 0 : 1;
	
		$labels = array();
		foreach ($this->labels as $label) {
			$position = $label->getPosition();
			if (isset($labels[$position])) {
				$savedDimension = $labels[$position]->getDimension();
				$dimension = $label->getDimension();
				if ($position === BCLabel::POSITION_LEFT || $position === BCLabel::POSITION_RIGHT) {
					if ($dimension[$searchLR] > $savedDimension[$searchLR]) {
						$labels[$position] = $label;
					}
				} else {
					if ($dimension[$searchTB] > $savedDimension[$searchTB]) {
						$labels[$position] = $label;
					}
				}
			} else {
				$labels[$position] = $label;
			}
		}

		return $labels;
	}
}
abstract class Barcode1D extends BarcodeBase {
	const SIZE_SPACING_FONT = 5;

	const AUTO_LABEL = '##!!AUTO_LABEL!!##';

	protected $thickness;
	protected $keys, $code;
	protected $positionX;
	protected $textfont;
	protected $text;
	protected $checksumValue;
	protected $displayChecksum;
	protected $label;					 
	protected $defaultLabel;			 

	protected function __construct() {
		parent::__construct();

		$this->setThickness(30);

		$this->defaultLabel = new BCLabel();
		$this->defaultLabel->setPosition(BCLabel::POSITION_BOTTOM);
		$this->setLabel(self::AUTO_LABEL);
		$this->setFont(new BCFontPhp(5));

		$this->text = '';
		$this->checksumValue = false;
	}

	public function getThickness() {
		return $this->thickness;
	}

	public function setThickness($thickness) {
		$this->thickness = intval($thickness);
		if ($this->thickness <= 0) {
			throw new BarcodeException('The thickness must be larger than 0. thickness');
		}
	}

	public function getLabel() {
		$label = $this->label;
		if ($this->label === self::AUTO_LABEL) {
			$label = $this->text;
			if ($this->displayChecksum === true && ($checksum = $this->processChecksum()) !== false) {
				$label .= $checksum;
			}
		}

		return $label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}

	public function getFont() {
		return $this->font;
	}

	public function setFont($font) {
		if (is_int($font)) {
			if ($font === 0) {
				$font = null;
			} else {
				$font = new BCFontPhp($font);
			}
		}

		$this->font = $font;
	}

	public function parse($text) {
		$this->text = $text;
		$this->checksumValue = false;		// Reset checksumValue
		$this->validate();

		parent::parse($text);

		$this->addDefaultLabel();
	}

	public function getChecksum() {
		return $this->processChecksum();
	}

	public function setDisplayChecksum($displayChecksum) {
		$this->displayChecksum = (bool)$displayChecksum;
	}

	protected function addDefaultLabel() {
		$label = $this->getLabel();
		$font = $this->font;
		if ($label !== null && $label !== '' && $font !== null && $this->defaultLabel !== null) {
			$this->defaultLabel->setText($label);
			$this->defaultLabel->setFont($font);
			$this->addLabel($this->defaultLabel);
		}
	}

	protected function validate() {
	}

	protected function findIndex($var) {
		return array_search($var, $this->keys);
	}

	protected function findCode($var) {
		return $this->code[$this->findIndex($var)];
	}

	protected function drawChar($im, $code, $startBar = true) {
		$colors = array(BarcodeBase::COLOR_FG, BarcodeBase::COLOR_BG);
		$currentColor = $startBar ? 0 : 1;
		$c = strlen($code);
		for ($i = 0; $i < $c; $i++) {
			for ($j = 0; $j < intval($code[$i]) + 1; $j++) {
				$this->drawSingleBar($im, $colors[$currentColor]);
				$this->nextX();
			}

			$currentColor = ($currentColor + 1) % 2;
		}
	}

	protected function drawSingleBar($im, $color) {
		$this->drawFilledRectangle($im, $this->positionX, 0, $this->positionX, $this->thickness - 1, $color);
	}

	protected function nextX() {
		$this->positionX++;
	}

	protected function calculateChecksum() {
		$this->checksumValue = false;
	}

	protected function processChecksum() {
		return false;
	}
}

class BCDraw {
	private $dpi;
	private $filename;	

	public function setFilename($filename) {
		$this->filename = $filename;
	}
	public function setDPI($dpi) {
		if(is_numeric($dpi)) {
			$this->dpi = max(1, $dpi);
		} else {
			$this->dpi = null;
		}
	}

	public function draw(BarcodeBase $barcode) {
		$size = $barcode->getDimension(0, 0);
		$w = max(1, $size[0]);
		$h = max(1, $size[1]);		
		$im = imagecreatetruecolor($w, $h);
		try {
			$color = $barcode->getColor($im, 0 );
			imagefilledrectangle ( $im, 0, 0, $w - 1, $h - 1, $color );
			$barcode->draw($im);
			ob_start ();
			imagepng ( $im );
			
			$bin = ob_get_contents ();
			ob_end_clean ();
			
			$this->setInternalProperties ( $bin );
			
			if (empty ( $this->filename )) {
				echo $bin;
			} else {
				@file_put_contents ( $this->filename, $bin );
			}
			@imagedestroy ( $im );
		} catch ( Exception $e ) {
			@imagedestroy ( $im );
		}
		
	}

	private function setInternalProperties(&$bin) {
		if(strcmp(substr($bin, 0, 8), pack('H*', '89504E470D0A1A0A')) === 0) {
			$chunks = $this->detectChunks($bin);

			$this->internalSetDPI($bin, $chunks);
			$this->internalSetC($bin, $chunks);
		}
	}

	private function detectChunks($bin) {
		$data = substr($bin, 8);
		$chunks = array();
		$c = strlen($data);
		
		$offset = 0;
		while($offset < $c) {
			$packed = unpack('Nsize/a4chunk', $data);
			$size = $packed['size'];
			$chunk = $packed['chunk'];

			$chunks[] = array('offset'=>$offset + 8, 'size'=>$size, 'chunk'=>$chunk);
			$jump = $size + 12;
			$offset += $jump;
			$data = substr($data, $jump);
		}
		
		return $chunks;
	}

	private function internalSetDPI(&$bin, &$chunks) {
		if($this->dpi !== null) {
			$meters = (int)($this->dpi * 39.37007874);

			$found = -1;
			$c = count($chunks);
			for($i = 0; $i < $c; $i++) {
				// We already have a pHYs
				if($chunks[$i]['chunk'] === 'pHYs') {
					$found = $i;
					break;
				}
			}

			$data = 'pHYs' . pack('NNC', $meters, $meters, 0x01);
			$crc = self::crc($data, 13);
			$cr = pack('Na13N', 9, $data, $crc);

			if($found == -1) {
				if($c >= 2 && $chunk[0]['chunk'] = 'IHDR') {
					array_splice($chunks, 1, 0, array(array('offset'=>33, 'size'=>9, 'chunk'=>'pHYs')));

					for($i = 2; $i < $c; $i++) {
						$chunks[$i]['offset'] += 21;
					}

					$firstPart = substr($bin, 0, 33);
					$secondPart = substr($bin, 33);
					$bin = $firstPart;
					$bin .= $cr;
					$bin .= $secondPart;
				}
			} else {
				$bin = substr_replace($bin, $cr, $chunks[$i]['offset'], 21);
			}
		}
	}

	private function internalSetC(&$bin, &$chunks) {
		if(count($chunks) >= 2 && $chunk[0]['chunk'] = 'IHDR') {
			$firstPart = substr($bin, 0, 33);
			$secondPart = substr($bin, 33);
			$cr = pack('H*', '0000004C74455874436F707972696768740047656E657261746564207769746820426172636F64652047656E657261746F7220666F722050485020687474703A2F2F7777772E626172636F64657068702E636F6D597F70B8');
			$bin = $firstPart;
			$bin .= $cr;
			$bin .= $secondPart;
		}
	}

	private static $crc_table = array();
	private static $crc_table_computed = false;

	private static function make_crc_table() {
		for($n = 0; $n < 256; $n++) {
			$c = $n;
			for ($k = 0; $k < 8; $k++) {
				if (($c & 1) == 1) {
					$c = 0xedb88320 ^ (self::SHR($c, 1));
				} else {
					$c = self::SHR($c, 1);
				}
			}
			self::$crc_table[$n] = $c;
		}

		self::$crc_table_computed = true;
	}

	private static function SHR($x, $n) {
		$mask = 0x40000000;

		if ($x < 0) {
			$x &= 0x7FFFFFFF;
			$mask = $mask >> ($n - 1);
			return ($x >> $n) | $mask;
		}

		return (int)$x >> (int)$n;
	}

	private static function update_crc($crc, $buf, $len) {
		$c = $crc;

		if (!self::$crc_table_computed) {
			self::make_crc_table();
		}

		for($n = 0; $n < $len; $n++) {
			$c = self::$crc_table[($c ^ ord($buf[$n])) & 0xff] ^ (self::SHR($c, 8));
		}

		return $c;
	}

	private static function crc($data, $len) {
		return self::update_crc(-1, $data, $len) ^ -1;
	}
}
class BCColor {
	protected $r, $g, $b;	 

	public function __construct() {
		$args = func_get_args();
		$c = count($args);
		if ($c === 3) {
			$this->r = intval($args[0]);
			$this->g = intval($args[1]);
			$this->b = intval($args[2]);
		} elseif ($c === 1) {
			if (is_string($args[0]) && strlen($args[0]) === 7 && $args[0]{0} === '#') {		// Hex Value in String
				$this->r = intval(substr($args[0], 1, 2), 16);
				$this->g = intval(substr($args[0], 3, 2), 16);
				$this->b = intval(substr($args[0], 5, 2), 16);
			} else {
				if (is_string($args[0])) {
					$args[0] = self::getColor($args[0]);
				}

				$args[0] = intval($args[0]);
				$this->r = ($args[0] & 0xff0000) >> 16;
				$this->g = ($args[0] & 0x00ff00) >> 8;
				$this->b = ($args[0] & 0x0000ff);
			}
		} else {
			$this->r = $this->g = $this->b = 0;
		}
	}

	public function r() {
		return $this->r;
	}

	public function g() {
		return $this->g;
	}

	public function b() {
		return $this->b;
	}

	public function allocate(&$im) {
		return imagecolorallocate($im, $this->r, $this->g, $this->b);
	}

	public static function getColor($code, $default = 'white') {
		switch(strtolower($code)) {
			case '':
			case 'white':
				return 0xffffff;
			case 'black':
				return 0x000000;
			case 'maroon':
				return 0x800000;
			case 'red':
				return 0xff0000;
			case 'orange':
				return 0xffa500;
			case 'yellow':
				return 0xffff00;
			case 'olive':
				return 0x808000;
			case 'purple':
				return 0x800080;
			case 'fuchsia':
				return 0xff00ff;
			case 'lime':
				return 0x00ff00;
			case 'green':
				return 0x008000;
			case 'navy':
				return 0x000080;
			case 'blue':
				return 0x0000ff;
			case 'aqua':
				return 0x00ffff;
			case 'teal':
				return 0x008080;
			case 'silver':
				return 0xc0c0c0;
			case 'gray':
				return 0x808080;
			default:
				return self::getColor($default, 'white');
		}
	}
}
class BCFontPhp {
	private $font;
	private $text;
	private $rotationAngle;
	private $backgroundColor;

	public function __construct($font) {
		$this->font = max(0, intval($font));
		$this->setRotationAngle(0);
		$this->setBackgroundColor(new BCColor('white'));
	}

	public function getText() {
		return $this->text;
	}

	public function setText($text) {
		$this->text = $text;
	}

	public function getRotationAngle() {
		return $this->rotationAngle;
	}

	public function setRotationAngle($rotationAngle) {
		$this->rotationAngle = (int)$rotationAngle;
		if ($this->rotationAngle !== 90 && $this->rotationAngle !== 180 && $this->rotationAngle !== 270) {
			$this->rotationAngle = 0;
		}
	}

	public function getBackgroundColor() {
		return $this->backgroundColor;
	}

	public function setBackgroundColor($backgroundColor) {
		$this->backgroundColor = $backgroundColor;
	}

	public function getDimension() {
		$w = imagefontwidth($this->font) * strlen($this->text);
		$h = imagefontheight($this->font);

		if ($this->rotationAngle === 90 || $this->rotationAngle === 270) {
			return array($h, $w);
		} else {
			return array($w, $h);
		}
	}

	public function draw($im, $color, $x, $y) {
		if ($this->rotationAngle !== 0) {
			if (!function_exists('imagerotate')) {
				throw new BarcodeException('The method imagerotate doesn\'t exist on your server. Do not use any rotation.');
			}
		
			$w = imagefontwidth($this->font) * strlen($this->text);
			$h = imagefontheight($this->font);
			$gd = imagecreatetruecolor($w, $h);
			imagefilledrectangle($gd, 0, 0, $w - 1, $h - 1, $this->backgroundColor->allocate($gd));
			imagestring($gd, $this->font, 0, 0, $this->text, $color);
			$gd = imagerotate($gd, $this->rotationAngle, 0);
			imagecopy($im, $gd, $x, $y, 0, 0, imagesx($gd), imagesy($gd));
		} else {
			imagestring($im, $this->font, $x, $y, $this->text, $color);
		}
	}
}

class BCFontFile {
	const PHP_BOX_FIX = 0;

	private $path;
	private $size;
	private $text = '';
	private $rotationAngle = 0;
	private $box;
	private $underlineX;
	private $underlineY;

	public function __construct($size,$font_name='Arial') {
		if($font_name){
			$font_name=ROOT_PATH . 'res'.DIRECTORY_SEPARATOR.'font'.DIRECTORY_SEPARATOR.$font_name.'.ttf';
			if(file_exists($font_name))
				$font_name=ROOT_PATH . 'res'.DIRECTORY_SEPARATOR.'font'.DIRECTORY_SEPARATOR.'Arial.ttf';
		}		
		$this->path = $font_name;
		$this->size = $size;
		$this->setRotationAngle(0);
	}

	public function getText() {
		return $this->text;
	}

	public function setText($text) {
		$this->text = $text;
		$this->rebuildBox();
	}

	public function getRotationAngle() {
		return $this->rotationAngle;
	}

	public function setRotationAngle($rotationAngle) {
		$this->rotationAngle = (int)$rotationAngle;
		if ($this->rotationAngle !== 90 && $this->rotationAngle !== 180 && $this->rotationAngle !== 270) {
			$this->rotationAngle = 0;
		}

		$this->rebuildBox();
	}

	public function getBackgroundColor() {
	}

	public function setBackgroundColor($backgroundColor) {
	}

	public function getDimension() {
		$w = 0.0;
		$h = 0.0;

		if ($this->box !== null) {
			$minX = min(array($this->box[0], $this->box[2], $this->box[4], $this->box[6]));
			$maxX = max(array($this->box[0], $this->box[2], $this->box[4], $this->box[6]));
			$minY = min(array($this->box[1], $this->box[3], $this->box[5], $this->box[7]));
			$maxY = max(array($this->box[1], $this->box[3], $this->box[5], $this->box[7]));
		
			$w = $maxX - $minX;
			$h = $maxY - $minY;
		}

		if ($this->rotationAngle === 90 || $this->rotationAngle === 270) {
			return array($h + self::PHP_BOX_FIX, $w);
		} else {
			return array($w + self::PHP_BOX_FIX, $h);
		}
	}

	public function draw($im, $color, $x, $y) {
		$drawingPosition = $this->getDrawingPosition($x, $y);
		imagettftext($im, $this->size, $this->rotationAngle, $drawingPosition[0], $drawingPosition[1], $color, $this->path, $this->text);
	}

	private function getDrawingPosition($x, $y) {
		$dimension = $this->getDimension();
		if ($this->rotationAngle === 0) {
			$y += abs(min($this->box[5], $this->box[7]));
		} elseif ($this->rotationAngle === 90) {
			$x += abs(min($this->box[5], $this->box[7]));
			$y += $dimension[1];
		} elseif ($this->rotationAngle === 180) {
			$x += $dimension[0];
			$y += abs(max($this->box[1], $this->box[3]));
		} elseif ($this->rotationAngle === 270) {
			$x += abs(max($this->box[1], $this->box[3]));
		}

		return array($x, $y);
	}

	private function rebuildBox() {
		$gd = imagecreate(1, 1);
		$this->box = imagettftext($gd, $this->size, 0, 0, 0, 0, $this->path, $this->text);

		$this->underlineX = abs($this->box[0]);
		$this->underlineY = abs($this->box[1]);

		if ($this->rotationAngle === 90 || $this->rotationAngle === 270) {
			$this->underlineX ^= $this->underlineY ^= $this->underlineX ^= $this->underlineY;
		}
	}
}

class BCLabel {
	const POSITION_TOP = 0;
	const POSITION_RIGHT = 1;
	const POSITION_BOTTOM = 2;
	const POSITION_LEFT = 3;

	const ALIGN_LEFT = 0;
	const ALIGN_TOP = 0;
	const ALIGN_CENTER = 1;
	const ALIGN_RIGHT = 2;
	const ALIGN_BOTTOM = 2;

	private $font;
	private $text;
	private $position;
	private $alignment;
	private $offset;
	private $spacing;
	private $rotationAngle;
	private $backgroundColor;

 
	public function __construct($text = '', $font = null, $position = self::POSITION_BOTTOM, $alignment = self::ALIGN_CENTER) {
		$font = $font === null ? new BCFontPhp(5) : $font;
		$this->setFont($font);
		$this->setText($text);
		$this->setPosition($position);
		$this->setAlignment($alignment);
		$this->setSpacing(4);
		$this->setOffset(0);
		$this->setRotationAngle(0);
		
		$this->setBackgroundColor(new BCColor('white'));
	}

 
	public function getText() {
		return $this->font->getText();
	}

 
	public function setText($text) {
		$this->text = $text;
		$this->font->setText($this->text);
	}

 
	public function getFont() {
		return $this->font;
	}
 
	public function setFont($font) {
		if ($font === null) {
			throw new BarcodeException('Font cannot be null. font');
		}

		$this->font = clone $font;
		$this->font->setText($this->text);
		$this->font->setRotationAngle($this->rotationAngle);
		$this->font->setBackgroundColor($this->backgroundColor);
	}
 
	public function getPosition() {
		return $this->position;
	}
 
	public function setPosition($position) {
		$position = intval($position);
		if ($position !== self::POSITION_TOP && $position !== self::POSITION_RIGHT && $position !== self::POSITION_BOTTOM && $position !== self::POSITION_LEFT) {
			throw new BarcodeException('The text position must be one of a valid constant. position');
		}

		$this->position = $position;
	}

 
	public function getAlignment() {
		return $this->alignment;
	}
 
	public function setAlignment($alignment) {
		$alignment = intval($alignment);
		if ($alignment !== self::ALIGN_LEFT && $alignment !== self::ALIGN_TOP && $alignment !== self::ALIGN_CENTER && $alignment !== self::ALIGN_RIGHT && $alignment !== self::ALIGN_BOTTOM) {
			throw new BarcodeException('The text alignment must be one of a valid constant. alignment');
		}

		$this->alignment = $alignment;
	}

 
	public function getOffset() {
		return $this->offset;
	}
 
	public function setOffset($offset) {
		$this->offset = intval($offset);
	}

 
	public function getSpacing() {
		return $this->spacing;
	}
 
	public function setSpacing($spacing) {
		$this->spacing = max(0, intval($spacing));
	}
 
	public function getRotationAngle() {
		return $this->font->getRotationAngle();
	}
 
	public function setRotationAngle($rotationAngle) {
		$this->rotationAngle = (int)$rotationAngle;
		$this->font->setRotationAngle($this->rotationAngle);
	}
 
	public function getBackgroundColor($backgroundColor) {
		return $this->font->getBackgroundColor();
	}
 
	public /*internal*/ function setBackgroundColor($backgroundColor) {
		$this->backgroundColor = $backgroundColor;
		$this->font->setBackgroundColor($this->backgroundColor);
	}
 
	public function getDimension() {
		$w = 0;
		$h = 0;

		$dimension = $this->font->getDimension();
		$w = $dimension[0];
		$h = $dimension[1];
		
		if ($this->position === self::POSITION_TOP || $this->position === self::POSITION_BOTTOM) {
			$h += $this->spacing;
			$w += max(0, $this->offset);
		} else {
			$w += $this->spacing;
			$h += max(0, $this->offset);
		}

		return array($w, $h);
	}

 
	public  function draw($im, $x1, $y1, $x2, $y2) {
		$x = 0;
		$y = 0;

		$fontDimension = $this->font->getDimension();

		if ($this->position === self::POSITION_TOP || $this->position === self::POSITION_BOTTOM) {
			if ($this->position === self::POSITION_TOP) {
				$y = $y1 - $this->spacing - $fontDimension[1];
			} elseif ($this->position === self::POSITION_BOTTOM) {
				$y = $y2 + $this->spacing;
			}

			if ($this->alignment === self::ALIGN_CENTER) {
				$x = ($x2 - $x1) / 2 + $x1 - $fontDimension[0] / 2 + $this->offset;
			} elseif ($this->alignment === self::ALIGN_LEFT)  {
				$x = $x1 + $this->offset;
			} else {
				$x = $x2 + $this->offset - $fontDimension[0];
			}
		} else {
			if ($this->position === self::POSITION_LEFT) {
				$x = $x1 - $this->spacing - $fontDimension[0];
			} elseif ($this->position === self::POSITION_RIGHT) {
				$x = $x2 + $this->spacing;
			}

			if ($this->alignment === self::ALIGN_CENTER) {
				$y = ($y2 - $y1) / 2 + $y1 - $fontDimension[1] / 2 + $this->offset;
			} elseif ($this->alignment === self::ALIGN_TOP)  {
				$y = $y1 + $this->offset;
			} else {
				$y = $y2 + $this->offset - $fontDimension[1];
			}
		}
		
		$this->font->setText($this->text);
		$this->font->draw($im, 0, $x, $y);
	}
}
