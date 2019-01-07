<?php
require_lib('img/gdimage',false);
class BarcodeUpcaEan13{
	/**
	 * @var int 标签文字大小，默认11
	 */
	public $font_size=11;
	/**
	 * @var string 标签字体，默认Arial
	 */
	public $font_name='Arial';
	/**
	 * @var int 条形码宽度，默认1
	 */
	public $bar_width=1;
	/**
	 * @var int 条形码高度，默认30像素
	 */
	public $bar_height=30;
	
	/**
	 * 生成UPCA条形码，如果条形码$text>12位，将取前12位。
	 * @param string $text		条形码数字串，>12位，将取前12位。
	 * @param string $file 		条形码文件保存路径，如果为NULL，导出为web的image/png，默认NULL
	 * @return boolean true成功，false失败
	 */
	function upca($text,$file=NULL) 
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
	    
	    $font=new GDFont($this->font_size,$this->font_name);
	    $font->set_text($text[0]);
	    $sz=$font->get_dimension();
	 	$hspace=$sz[1]-2;
	 	$wspace=$sz[0]+4;
	 	
	    /* create image */ 
	    $w=$this->bar_width*95+$wspace*2;$h=$this->bar_height+$sz[1]*2;
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
				imagefilledrectangle ( $img, ($i * $this->bar_width) + $wspace, 5, ($i + 1) * $this->bar_width + $wspace - 1, $this->bar_height + 5 + $sh, $color ); //
			}
			
			/* add label*/
			$font->set_text ( $text [0] );
			$font->draw ( $img, $fg, 2, $this->bar_height - $hspace );
			for($i = 0; $i < 5; $i ++) {
				$font->set_text ( $text [$i + 1] );
				$font->draw ( $img, $fg, $this->bar_width * (13 + $i * 6) + $wspace, $this->bar_height + $hspace );
				$font->set_text ( $text [$i + 6] );
				$font->draw ( $img, $fg, $this->bar_width * (53 + $i * 6) + $wspace, $this->bar_height + $hspace );
			}
			$font->set_text ( $text [11] );
			$font->draw ( $img, $fg, $w - $sz [0] - 2, $this->bar_height - $hspace ); //$lw*95+17
			
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
	 * @param integer $pad		   
	 * @param string $file 条形码文件保存路径，如果为NULL，导出为web的image/png，默认NULL
	 * @return boolean true成功，false失败
	 */
	function ean13($text,$file=NULL) {
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
		
		$font = new GDFont ( $this->font_size, $this->font_name );
		$font->set_text ( $text [0] );
		$sz = $font->get_dimension ();
		$hspace = $sz [1] - 2;
		$pad=2;	//填充空白
		$w_offset = $sz [0] + $pad;
		$w = $this->bar_width * 95 + $w_offset * 2;
		$h = $this->bar_height + $sz [1] * 2 + $pad;
		
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
				imagefilledrectangle ( $img, ($i * $this->bar_width) + $w_offset, $pad, 
					($i + 1) * $this->bar_width + $w_offset - 1, $this->bar_height + $pad + $sh, $color );
			}
			$font->set_text ( $text [0] );
			$font->draw ( $img, $fg, $w_offset - $sz [1], $this->bar_height + $hspace );
			for($i = 0; $i < 6; $i ++) {
				$font->set_text ( $text [$i + 1] );
				$font->draw ( $img, $fg, $this->bar_width * (8 + $i * 6) + $w_offset, 
					$this->bar_height + $hspace );
				$font->set_text ( $text [$i + 7] );
				$font->draw ( $img, $fg, $this->bar_width * (53 + $i * 6) + $w_offset,
				 	$this->bar_height + $hspace );
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
}
