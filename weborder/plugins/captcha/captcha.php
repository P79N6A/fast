<?PHP
/**
說明:
gif验证码类
调用:
		<img src="class_code.php?code=code" onclick="this.src=this.src+'&'+Math.round(Math.random(0)*1000)" style="cursor:pointer;">
验证:
IF(!isSet($_SESSION['code']) Or StrToLower($_SESSION['code'])!=StrToLower($cod)){
throw New Exception('Error:'.__LINE__.',驗證碼錯誤!');Die();
}unSet($cod,$_SESSION['code']);
/**/

//GIF类
Class GIF{
		Private Static $Txt='';			//GIF mess
		Private Static $Img='GIF89a';	//GIF header 6 bytes
		Private Static $Debug=False;	//Is open Debug?
		Private Static $BUF=Array();
		Private Static $LOP= 0;
		Private Static $DIS= 2;
		Private Static $COL=-1;
		Private Static $IMG=-1;

		/**
			生成GIF图片验证
			@param $W 宽度
			@param $H 高度
			@param $B 背景色
		/**/
		Public Static Function init($W=75,$H=25,$B=''){
			$chars='bcdefhkmnrstuvwxyABCDEFGHKMNPRSTUVWXY34568';
			For($i=0;$i<4;$i++){
				self::$Txt .= substr($chars,mt_rand(0,strlen($chars)-1),1);
			}unSet($chars);
			CTX()->set_session('captcha_code',strtolower(self::$Txt));			// 记录session,全部小寫
			IF($B=='' Or StriStr($B,',')==False Or Substr_Count($B,',')!=2){$B='255,255,255';}
			$B=Explode(',',$B);

			//生成一个多帧的GIF动画
			For($i=0;$i<7;$i++){
				$Im=ImageCreate($W,$H);

				//背景
				$bg=ImageColorAllocate($Im,$B[0],$B[1],$B[2]);
				ImageColorTransparent($Im,$bg);
				unSet($bg);

				IF($i==0) {//第一幀為干擾碼
					$txt=ImageColorAllocate($Im,35,35,35);
						ImageTTFtext($Im,25,Rand(-15,25),8,($H-3),$txt,ROOT_PATH.  CTX()->app_name .'/plugins/captcha/class_code.ttf',self::$Txt[0]);
						ImageTTFtext($Im,25,Rand(-10,20),21,($H-4),$txt,ROOT_PATH.  CTX()->app_name .'/plugins/captcha/class_code.ttf',self::$Txt[1]);
						ImageTTFtext($Im,25,Rand(-25,15),34,($H-4),$txt,ROOT_PATH.  CTX()->app_name .'/plugins/captcha/class_code.ttf',self::$Txt[2]);
                                                ImageTTFtext($Im,25,Rand(-30,10),47,($H-4),$txt,ROOT_PATH.  CTX()->app_name .'/plugins/captcha/class_code.ttf',self::$Txt[3]);
					unSet($txt);
				}Else{//驗證碼
					$txt=ImageColorAllocate($Im,35,35,35);
						//ImageString($Im,7,Rand(0,($W/2)),Rand(-3,5),self::$Txt,$txt);
						ImageTTFtext($Im,25,Rand(-15,25),8,($H-3),$txt,ROOT_PATH.  CTX()->app_name .'/plugins/captcha/class_code.ttf',self::$Txt[0]);
						ImageTTFtext($Im,25,Rand(-10,20),21,($H-4),$txt,ROOT_PATH.  CTX()->app_name .'/plugins/captcha/class_code.ttf',self::$Txt[1]);
						ImageTTFtext($Im,25,Rand(-25,15),34,($H-4),$txt,ROOT_PATH.  CTX()->app_name .'/plugins/captcha/class_code.ttf',self::$Txt[2]);
                                                ImageTTFtext($Im,25,Rand(-30,10),47,($H-4),$txt,ROOT_PATH.  CTX()->app_name .'/plugins/captcha/class_code.ttf',self::$Txt[3]);
					unSet($txt);
				}
				ImageGif($Im);Imagedestroy($Im);

				$Imdata[]=ob_get_contents();
				OB_clean();
			}unSet($W,$H,$B);
			IF(self::$Debug){Echo '<pre>',Var_Dump($Imdata),'</pre>';Die();}
			Header('Content-type:image/gif');
			Return GIF::CreatGif($Imdata,20);
			unSet($Imdata);
		}

		Private Static Function CreatGif($GIF_src,$GIF_dly=10,$GIF_lop=0,$GIF_dis=0,$GIF_red=0,$GIF_grn=0,$GIF_blu=0,$GIF_mod='bin'){
			IF(!is_array($GIF_src) && !is_array($GIF_tim)){
				throw New Exception('Error:'.__LINE__.',Does not supported function for only one image!!');Die();
			}
			self::$LOP=($GIF_lop>-1) ? $GIF_lop : 0;
			self::$DIS=($GIF_dis>-1) ? (($GIF_dis<3) ? $GIF_dis : 3) : 2;
			self::$COL=($GIF_red>-1 && $GIF_grn>-1 && $GIF_blu>-1) ? ($GIF_red | ($GIF_grn << 8) | ($GIF_blu << 16)) : -1;
			For($i=0,$src_count=count($GIF_src);$i<$src_count;$i++){
				IF(strToLower($GIF_mod) == 'url'){
					self::$BUF[]=fread(fopen($GIF_src[$i],'rb'),filesize($GIF_src[$i]));
				}Elseif(strToLower($GIF_mod) == 'bin'){
					self::$BUF[]=$GIF_src[$i];
				}Else{
					throw New Exception('Error:'.__LINE__.',Unintelligible flag ('.$GIF_mod.')!');Die();
				}
				IF(!(Substr(self::$BUF[$i],0,6)=='GIF87a' Or Substr(self::$BUF[$i],0,6)=='GIF89a')){
					throw New Exception('Error:'.__LINE__.',Source '.$i.' is not a GIF image!');Die();
				}
				For($j=(13+3*(2 << (ord(self::$BUF[$i]{10})& 0x07))),$k=TRUE;$k;$j++){
					switch(self::$BUF[$i]{$j}){
					case '!':
						IF((substr(self::$BUF[$i],($j+3),8))== 'NETSCAPE'){
							throw New Exception('Error:'.__LINE__.',Could not make animation from animated GIF source ('.($i+1).')!');Die();
						}
						break;
					case ';':
						$k=FALSE;
						break;
					}
				}
			}
			GIF::AddHeader();
			For($i=0,$count_buf=count(self::$BUF);$i<$count_buf;$i++){
				GIF::AddFrames($i,$GIF_dly);
			}
			self::$Img .= ';';
			Return (self::$Img);
		}

		Private Static Function AddHeader(){
			$i=0;
			IF(ord(self::$BUF[0]{10}) & 0x80){
				$i=3*(2 << (ord(self::$BUF[0]{10}) & 0x07));
				self::$Img .= substr(self::$BUF[0],6,7);
				self::$Img .= substr(self::$BUF[0],13,$i);
				self::$Img .= "!\377\13NETSCAPE2.0\3\1".chr(self::$LOP & 0xFF).chr((self::$LOP >> 8) & 0xFF)."\0";
			}unSet($i);
		}

		Private Static Function AddFrames($i,$d){
			$L_str=13+3*(2 <<(ord(self::$BUF[$i]{10}) & 0x07));
			$L_end=strlen(self::$BUF[$i])-$L_str-1;
			$L_tmp=substr(self::$BUF[$i],$L_str,$L_end);
			$G_len=2 << (ord(self::$BUF[0]{10}) & 0x07);
			$L_len=2 << (ord(self::$BUF[$i]{10}) & 0x07);
			$G_rgb=substr(self::$BUF[0],13,3*(2 << (ord(self::$BUF[0]{10}) & 0x07)));
			$L_rgb=substr(self::$BUF[$i],13,3*(2 << (ord(self::$BUF[$i]{10}) & 0x07)));
			$L_ext="!\xF9\x04".chr((self::$DIS << 2)+ 0).chr(($d >> 0) & 0xFF).chr(($d >> 8) & 0xFF)."\x0\x0";
			IF(self::$COL>-1 && ord(self::$BUF[$i]{10}) & 0x80){
				For($j=0;$j<(2 << (ord(self::$BUF[$i]{10}) & 0x07));$j++){
					IF(ord($L_rgb{3*$j+0})==(self::$COL >>  0) & 0xFF && ord($L_rgb{3*$j+1})== (self::$COL >>  8) & 0xFF && ord($L_rgb{3*$j+2}) == (self::$COL >> 16) & 0xFF){
						$L_ext="!\xF9\x04".chr((self::$DIS << 2)+1).chr(($d >> 0) & 0xFF).chr(($d >> 8) & 0xFF).chr($j)."\x0";
						break;
					}
				}
			}
			switch($L_tmp{0}){
			case '!':
				$L_img=substr($L_tmp,8,10);
				$L_tmp=substr($L_tmp,18,strlen($L_tmp)-18);
				break;
			case ',':
				$L_img=substr($L_tmp,0,10);
				$L_tmp=substr($L_tmp,10,strlen($L_tmp)-10);
				break;
			}
			IF(ord(self::$BUF[$i]{10}) & 0x80 && self::$IMG>-1){
				IF($G_len == $L_len){
					IF(GIF::Compare($G_rgb,$L_rgb,$G_len)){
						self::$Img .= ($L_ext.$L_img.$L_tmp);
					}Else{
						$byte =ord($L_img{9});
						$byte |= 0x80;
						$byte &= 0xF8;
						$byte |= (ord(self::$BUF[0]{10}) & 0x07);
						$L_img{9}=chr($byte);
						self::$Img .= ($L_ext.$L_img.$L_rgb.$L_tmp);
					}
				}Else{
					$byte =ord($L_img{9});
					$byte |= 0x80;
					$byte &= 0xF8;
					$byte |= (ord(self::$BUF[$i]{10}) & 0x07);
					$L_img{9}=chr($byte);
					self::$Img .= ($L_ext.$L_img.$L_rgb.$L_tmp);
				}
			}Else{
				self::$Img .= ($L_ext.$L_img.$L_tmp);
			}
			self::$IMG =1;
		}

		Private Static Function Compare($G_Block,$L_Block,$Len){
			For($i=0;$i<$Len;$i++){
				IF($G_Block{3*$i+0} != $L_Block{3*$i+0} || $G_Block{3*$i+1} != $L_Block{3*$i+1} || $G_Block{3*$i+2} != $L_Block{3*$i+2}){
					Return (0);
				}
			}
			Return (1);
		}
	}