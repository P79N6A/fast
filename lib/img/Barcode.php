<?php
class Barcode{
	/**
	 * @var string 标签字体，默认Arial
	 */
	public $fontName='Arial';
	/**
	 * @var int 标签文字大小，默认11
	 */
	public $fontSize=11;
	/**
	 * @var int 条形码宽度，默认1
	 */
	public $barWidth=1;
	/**
	 * @var int 条形码高度，默认30像素
	 */
	public $barHeight=30;
	/**
	 * @var char 水平 ，L，M，Q，H
	 */
	public $qrLevel='L';
	/**
	 * @var int 大小
	 */
	public $qrSize=3;
	/**
	 * @var int  边距
	 */
	public $qrMargin=4;
	
	/**
	 * @var int 条码128
	 */
	const Code128	=0;
	/**
	 * @var int 二维码
	 */
	const CodeQR	=7;	
		
	const Code128A	=1;
	const Code128B	=2;
	const Code128C	=3;	
	const Code39	=4;
	const CodeUpca	=5;
	const CodeEan13	=6;	

	/**
	 * @var int 条目类型  包括：Code128，Code128A，Code128B，Code128C，Code39，CodeUpca，CodeEan13
	 */
	public $codeType=self::Code128;
	/**
	 * @var 图像类型 ，PNG，仅二维码有效
	 */
	const ImgPNG=0;
	/**
	 * @var 图像类型 ，GIF，仅二维码有效
	 */
	const ImgGIF=1;
	/**
	 * @var int 条目类型  包括：Code128，Code128A，Code128B，Code128C，Code39，CodeUpca，CodeEan13
	 */
	public $imgType=self::ImgPNG;	
	/**
	 * 输出条码
	 * @param string $code  条码
	 * @param string $filename  条码图像输出文件，如果为NULL，输出到web
	 */
	function output($code,$filename=NULL){
		require_lib('img/extra/Barcode128_39',false);
		if(! $code) return false;
		
		if($this->codeType==self::Code128 ||
			$this->codeType==self::Code128A ||
			$this->codeType==self::Code128B ||
			$this->codeType==self::Code128C||
			$this->codeType==self::Code39){
				
			if($this->codeType==self::Code39)
				$c=new Code39();
			else{
				$c=new Code128();
				if($this->codeType==self::Code128A) $c->setStart('A');
				elseif($this->codeType==self::Code128B) $c->setStart('B');
			}  
			$c->setScale($this->barWidth);
			$c->setThickness($this->barHeight); 
			
			$font = new BCFontFile($this->fontSize,$this->fontName);
			$c->setFont($font); 
			$c->parse($code);	
			$c->output($filename);	
		}elseif($this->codeType==self::CodeUpca || $this->codeType==self::CodeEan13){
			require_lib('img/extra/BarcodeUpcaEan13',false);
			$c=new BarcodeUpcaEan13();
			$c->fontSize=$this->fontSize;
			$c->fontName=$this->fontName;
			$c->barWidth=$this->barWidth;
			$c->barHeight=$this->barHeight;
			if($this->codeType==self::CodeUpca)
				$c->upca($code,$filename);
			elseif($this->codeType==self::CodeEan13)
				$c->ean13($code,$filename);
		}else{
			require_lib ( "img/extra/phpqrcode",false );
			if($this->imgType==self::ImgGIF)
				QRcode::gif($code, $filename ? $filename :false,$this->qrLevel,$this->qrSize,$this->qrMargin);
			else QRcode::png($code, $filename ? $filename :false,$this->qrLevel,$this->qrSize,$this->qrMargin);
		}
	}
}
