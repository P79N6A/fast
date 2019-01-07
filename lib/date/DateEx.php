<?php
/**
 * 日期相关函数
 */
class DateEx{
	/**
	 * @var DateTime  日期时间对象
	 */
	private $date;
	/**
	 * 对象构建
	 * @param DateTime|int|string|NULL $date 时间日期参数   DateTime：时间日期对象，int：时间戳timestamp，
	 * string：时间日期字符串，NULL：当前系统时间
	 */
	function __construct($date=NULL){
		if(is_object($date)) $this->date=$date;	//Date obj
		elseif(is_int($date)){	//timestamp
			$this->date=new DateTime(); 
			$this->date->setTimestamp($date);
		} elseif(is_string($date)) $this->date=new DateTime($date);
		else $this->date =new DateTime();  //now
	}
	function __toString(){
		return $this->toString(true);
	}
	
	/**
	 * 返回标准时间字符串
	 * @param boolean $timePart 返回字符串是否包括时间部分，默认false
	 * @return string 标准日期时间字符串
	 */
	function toString($timePart=false) {
		return $timePart ? date_format($this->date,'Y-m-d H:i:s') : date_format($this->date,'Y-m-d');
	}
	/**
	 * 得到时间日期对象
	 *@return DateTime  
	 */
	function getDateTime(){
		return $this->date;
	}
	/**
	 * 得到时间日期时间戳
	 *@return int  
	 */
	function getTimestamp(){
		return $this->date->getTimestamp();
	}
	/**
	 * 返回格式化日期时间
	 * @param string $format 格式化模板 ，格式@see date()
	 *@return string  
	 */
	function format($format){
		return $this->date->format($format);
	}	
	/**
	 * 得当调整到后面或者前面$num的DateEx对象
	 * @param integer $num  添加或者减少的间隔，+添加,-减少
	 * @param string $part  添加或者减少的部位，y:年；m:月；d:天；h:小时；n:分钟；s:秒；w：周。默认为天
	 * @return DateEx 返回调整后的新对象 
	 */
	function change($num,$part='d') {
		$r=clone $this;
		if(! $num) return $r;
		$is_sub=$num<0;
		if($is_sub) $num=abs($num);
		
		switch ($part){
			case 'y':	$intv="P{$num}Y";break;
			case 'm':	$intv="P{$num}M";break;
			case 'd':	$intv="P{$num}D";break;
			case 'h':	$intv="PT{$num}H";break;
			case 'n':	$intv="PT{$num}M";break;
			case 's':	$intv="PT{$num}S";break;
			case 'w':	$intv="P{$num}W";break;
		}
		if($is_sub) $r->date->sub(new DateInterval($intv));
		else $r->date->add(new DateInterval($intv));
		return $r;
	}
	/**
	 * 得到所在周一的DateEx对象
	 * @return DateEx 周一DateEx对象
	 */
	function firstDayOfWeek() {
		$r=clone $this;
		$num=date('w',$r->getTimestamp())-1;
		if($num<0) $num=6;
		$r->date->sub(new DateInterval("P{$num}D"));
		$r->date->setTime(0,0,0);
		return $r ;
	}
	/**
	 * 得到所在周末的DateEx对象
	 * @return DateEx 周末DateEx对象
	 */
	function lastDayOfWeek() {
		$r=clone $this;
		$num=7-date('w',$r->getTimestamp());
		if($num<0) $num=6;
		$r->date->add(new DateInterval("P{$num}D"));
		$r->date->setTime(23,59,59);
		return $r ;
	}	
	/**
	 * @return boolean 是否为闰年
	 */
	function isLeap() {
		$year=$this->date->format('Y');
		return ((($year % 4) == 0) && (($year % 100) != 0) || (($year % 400) == 0));
	}
	/**
	 * 得到所在月份第一天
	 * @return DateEx 月份第一天DateEx对象
	 */
	function firstDayOfMonth() {
		$r=clone $this;
		$d=date_parse($r);
		$r->date->setDate($d['year'],$d['month'],1);
		$r->date->setTime(0,0,0);
		return $r;
	}
	/**
	 * 得到所在月份最后一天
	 * @return DateEx 月份最后一天DateEx对象
	 */
	function lastDayofMonth() {
		$r=clone $this;
		$d=date_parse($r);
		$r->date->setDate($d['year'],$d['month']+1,0);
		$r->date->setTime(23,59,59);
		return $r;
	}
	/**
	 * 得到所在月份天数
	 * @return integer 月份天数
	 */
	function daysOfMonth() {
		return intval($this->lastDayofMonth()->date->format("d"));
	}
	
	/**
	 * 得到所在年份第一天
	 * @return DateEx 年份第一天DateEx对象
	 */
	function firstDayOfYear() {
		$r=clone $this;
		$d=date_parse($r);
		$r->date->setDate($d['year'],1,1);
		$r->date->setTime(0,0,0);	
		return $r;	
	}
	/**
	 * 得到所在年份最后一天
	 * @return DateEx 年份最后DateEx对象
	 */
	function lastDayOfYear() {
		$r=clone $this;
		$d=date_parse($r);
		$r->date->setDate($d['year']+1,1,0);
		$r->date->setTime(23,59,59);	
		return $r;		
	}
	/**
	 * 公历转农历，从1950年开始
	 * @param Date $date 公历日期
	 * @param boolean $upper=true 是否大写
	 */
	function toLunar($upper=true){
		require_lib('date/extra/DateLunar');
		$d=new DateLunar();
		return $d->S2L($this,$upper);
	}
	/**
	 *农历转公历，从1950年开始
	 * @param string $date 农历日期   如2013-12-31
	 * @param boolean $is_leap   查询月份是否农历闰月  如闰四月，而不是四月
	 * @return DateEx 公历DateEx对象
	 */
	static function lunarToDate($date=NULL,$is_leap = false){
		require_lib('date/extra/DateLunar');
		$d=new DateLunar();
		return new DateEx($d->L2S($date,$is_leap));
	}
	/**
	 * 将日期各部位阿拉伯数字转换为中文 
	 * @param integer|string $num 日期各部位包括月、日、周、时、分、秒
	 * @param boolean $upper 是否大写，如贰拾壹
	 * @return string 中文
	 */
	static function partToLocal($num,$upper=false){
		$local =$upper ? lang('date_local_upper') : lang('date_local_char');
		$num = intval($num);
		$result = '';
		$two=($num - $num % 10)/10;
		$num=$num % 10;
		if($two<=0) $result = $local[$num] ;
		else{
			if($num===0) $result=$local[$two].$local[10];
			elseif($two===1) $result=$local[10].$local[$num];
			else $result=$local[$two].$local[10].$local[$num];
		} 
		return $result;
	}
	/**
	 * 将年份阿拉伯数字转换为中文 
	 * @param integer|string $year 年份
	 * @param boolean $upper 是否大写，如贰零壹壹
	 * @return string 中文
	 */
	private function yearToLocal( $year,$upper=false){
		$local =$upper ? lang('date_local_upper') : lang('date_local_char');
		if(! is_string($year)) $year=strval($year);
		$result = '';
		for($i=0;$i<4;$i++)  $result .= $local[substr($year,$i,1)];
		return $result;
	}
	/**
	 * 将日期阿拉伯数字转换为中文 
	 * @param boolean $format 格式 ， y:年，m：月，d：日，h:时，i：分，s：秒，D：日期，T：时间，包含格式字符即出现
	 * @param boolean $upper 是否大写，如贰零壹壹
	 * @return string 中文
	 */
	function toLocal($format='DT',$upper=false){
		$unit =lang('date_local_unit');
		list($date,$time)=explode(' ',$this);
		$date=explode('-',$date);
		$time=explode(':',$time);
		
		$d=$t='';
		if(strpos($format,'y')!==false || strpos($format,'D')!==false )
			$d .=$this->yearToLocal($date[0],$upper).$unit[0];
		if(strpos($format,'m')!==false  || strpos($format,'D')!==false )
			$d .=$this->partToLocal($date[1],$upper).$unit[1];
		if(strpos($format,'d')!==false  || strpos($format,'D')!==false )
			$d .=$this->partToLocal($date[2],$upper).$unit[2];
			
		if(strpos($format,'h')!==false  || strpos($format,'T')!==false )
			$t .=$this->partToLocal($time[0],$upper).$unit[4];
		if(strpos($format,'i')!==false  || strpos($format,'T')!==false )
			$t .=$this->partToLocal($time[1],$upper).$unit[5];
		if(strpos($format,'s')!==false  || strpos($format,'T')!==false )
			$t .=$this->partToLocal($time[2],$upper).$unit[6];
			
		if($t && $d) return $d.$unit[3].$t;
		else if($d)	return $d;
		else return $t;
	}
	/**
	 * 得到年度的天干地支
	 * @param integer $year 年度 ，如果为NULL，当前年份
	 * @return 返回对应的天干地支
	 */    
	function toTgdz($year=NULL){
		if(! $year) $year=$this->format('Y');
		$cn_gz = array(array('甲','乙','丙','丁','戊','己','庚','辛','壬','癸'),
					  array('子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'));
		$i= $year -1744;
		return $cn_gz[0][$i%10].$cn_gz[1][$i%12];	
	}
	/**
	 * 得到年度的生肖
	 * @param integer $year 年度  ，如果为NULL，当前年份
	 * @return 返回对应的天干地支
	 */    
	function toSx($year=NULL){
		if(! $year) $year=$this->format('Y');
		$cn_sx = array('鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪');
		return $cn_sx[($year-4)%12];	
	}
	/**
	 * 得到日期对应的星座
	 * @return 返回对应的星座
	 */
	function toStar(){
		$date=date_parse($this);	
		$m=$date['month'];$d=$date['day'];
		$star=array('摩羯','宝瓶','双鱼','白羊','金牛','双子','巨蟹','狮子','处女','天秤','天蝎','射手');
	 	$zone   = array(1222,122,222,321,421,522,622,722,822,922,1022,1122,1222);
		if((100*$m+$d)>=$zone[0]||(100*$m+$d)<$zone[1])   $i=0;
		else for($i=1;$i<12;$i++){
			if((100*$m+$d)>=$zone[$i]&&(100*$m+$d)<$zone[$i+1])	break;
		}
		return $star[$i].'座';		
	}
}

