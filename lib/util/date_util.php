<?php
/**
 * 从时间戳或日期对象得到标准时间字符串
 * @param DateTime | intger $timestamp 时间戳或日期对象,默认为当前时间
 * @param boolean $need_time 返回字符串是否包括时间部分，默认true
 * @return string 标准时间字符串
 */
function date_to_str($timestamp=NULL,$need_time=true) {
	if(! $timestamp) $timestamp=time();
	if(is_object($timestamp)) return $need_time ? date_format($timestamp,'Y-m-d H:i:s') : date_format($timestamp,'Y-m-d');
	else return $need_time ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d', $timestamp);
}
/**
 * 得到$date后面或者前面$num的时间
 * @param integer $num  添加或者减少的间隔
 * @param string $part  添加或者减少的部位，y:年；m:月；d:天；h:小时；n:分钟；s:秒。默认为天
 * @param Date|string|integer $date 时间，如果为NULL，得到当前时间,默认为当前时间
 * @param boolean $need_time 返回字符串是否包括时间部分，默认false
 */
function date_change($num,$part='d',$date=NULL,$need_time=false) {
	if(!$date || ! is_object($date)) $date=new DateTime(date_to_str($date));
	if(! $num) return date_to_str($date,$need_time);
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
	if($is_sub) $date->sub(new DateInterval($intv));
	else $date->add(new DateInterval($intv));
	return date_to_str($date,$need_time);
}
/**
 * 得到$date所在周一
 * @param Date|string|integer $date 时间，如果为NULL，得到当前时间,默认为当前时间
 * @param boolean $need_time 返回字符串是否包括时间部分，默认false
 * @return string 周第一天标准时间字符串
 */
function date_week_first_day($date=NULL,$need_time=false) {
	if(! $date || ! is_string($date)) $date=date_to_str($date);
	$date=new DateTime($date);
	$num=date('w',$date->getTimestamp())-1;
	if($num<0) $num=6;
	$date->sub(new DateInterval("P{$num}D"));
	return date_to_str($date,$need_time) ;
}
/**
 * 是否为闰年，如果为空，判断当前年份
 * @return boolean
 */
function date_is_leap($year =NULL) {
	if(! $year) $year=date('Y',time());
	return ((($year % 4) == 0) && (($year % 100) != 0) || (($year % 400) == 0));
}
/**
 * 得到$date所在月份第一天
 * @param Date|string|integer $date 时间，如果为NULL，得到当前时间,默认为当前时间
 * @param boolean $need_time 返回字符串是否包括时间部分，默认false
 * @return string 月份第一天标准时间字符串
 */
function date_month_first_day($date=NULL,$need_time=false) {
	if(! $date || ! is_string($date)) $date=date_to_str($date);
	$date=date_parse($date);
	if($need_time) return date("Y-m-d H:i:s", mktime(0,0,0,$date['month'], 1,$date['year'])) ;
	else return date("Y-m-d", mktime(0,0,0,$date['month'], 1,$date['year'])) ;
}
/**
 * 得到$date所在月份最后一天
 * @param Date|string|integer $date 时间，如果为NULL，得到当前时间,默认为当前时间
 * @param boolean $need_time 返回字符串是否包括时间部分，如果包含时间，返回最后一秒，默认false
 * @return string 月份最后一天标准时间字符串
 */
function date_month_last_day($date=NULL,$need_time=false) {
	if(! $date ||! is_string($date)) $date=date_to_str($date);
	$date=date_parse($date);
	if($need_time) return date("Y-m-d H:i:s", mktime(23,59,59,$date['month'] + 1, 0,$date['year'])) ;
	else return date("Y-m-d", mktime(0,0,0,$date['month'] + 1, 0,$date['year'])) ;
}
/**
 * 得到$date所在月份天数
 * @param Date|string|integer $date 时间，如果为NULL，得到当前时间,默认为当前时间
 * @return integer 月份天数
 */
function date_days_of_month($date=NULL) {
	if(! $date || ! is_string($date)) $date=date_to_str($date);
	$date=date_parse($date);
	return intval(date("d", mktime(0,0,0,$date['month'] + 1, 0,$date['year'])));
}

/**
 * 得到$date所在年份第一天
 * @param Date|string|integer $date 时间，如果为NULL，得到当前时间,默认为当前时间
 * @param boolean $need_time 返回字符串是否包括时间部分，默认false
 * @return string 年份第一天标准时间字符串
 */
function date_year_first_day($date=NULL,$need_time=false) {
	if(! $date || ! is_string($date)) $date=date_to_str($date);
	$date=date_parse($date);
	if($need_time) return date("Y-m-d H:i:s", mktime(0,0,0, 1, 1,$date['year'])) ;
	else return date("Y-m-d", mktime(0,0,0, 1, 1,$date['year'])) ;
}
/**
 * 得到$date所在年份最后一天
 * @param Date|string|integer $date 时间，如果为NULL，得到当前时间,默认为当前时间
 * @param boolean $need_time 返回字符串是否包括时间部分，如果包含时间，返回最后一秒，默认false
 * @return string 年份最后一天标准时间字符串
 */
function date_year_last_day($date=NULL,$need_time=false) {
	if(! $date || ! is_string($date)) $date=date_to_str($date);
	$date=date_parse($date);
	if($need_time) return date("Y-m-d H:i:s", mktime(23,59,59,1,0,$date['year']+1)) ;
	else return date("Y-m-d", mktime(0,0,0, 1, 0,$date['year']+1)) ;
}

/**
 * 将日期各部位阿拉伯数字转换为中文 
 * @param integer|string $num 日期各部位包括月、日、周、时、分、秒
 * @param boolean $upper 是否大写，如贰拾壹
 * @return string 中文
 */
function date_part_to_local($num,$upper=false){
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
function date_year_to_local( $year,$upper=false){
	$local =$upper ? lang('date_local_upper') : lang('date_local_char');
	if(! is_string($year)) $year=strval($year);
	$result = '';
	for($i=0;$i<4;$i++)  $result .= $local[substr($year,$i,1)];
	return $result;
}
/**
 * 将日期阿拉伯数字转换为中文 
 * @param Date|string|integer $date 时间，如果为NULL，得到当前时间,默认为当前时间
 * @param boolean $need_time 返回字符串是否包括时间部分，默认false
 * @return string 中文
 */
function date_to_local($date=NULL,$need_time=false,$upper=false){
	$unit =lang('date_local_unit');
	$d=date_to_str($date,$need_time);
	if($need_time){
		list($date,$time)=explode(' ',$d);
		$date=explode('-',$date);
		$time=explode(':',$time);
	}else $date=explode('-',$d);
	
	
	$d=date_year_to_local($date[0],$upper).$unit[0].date_part_to_local($date[1],$upper).$unit[1].date_part_to_local($date[2],$upper).$unit[2];
	if($need_time){
		$t=date_part_to_local($time[0],$upper).$unit[4].date_part_to_local($time[1],$upper).$unit[5].date_part_to_local($time[2],$upper).$unit[6];
		return $d.$unit[3].$t;
	}
	else return $d;
}
/**
 * 得到年度的天干地支
 * @param integer $year 年度
 * @return 返回对应的天干地支
 */    
function date_to_zh_gz($year){
	$cn_gz = array(array('甲','乙','丙','丁','戊','己','庚','辛','壬','癸'),
				  array('子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'));
	$i= $year -1744;
	return $cn_gz[0][$i%10].$cn_gz[1][$i%12];	
}
/**
 * 得到年度的生肖
 * @param integer $year 年度
 * @return 返回对应的天干地支
 */    
function date_to_zh_sx($year){
	$cn_sx = array('鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪');
	return $cn_sx[($year-4)%12];	
}
/**
 * 得到日期对应的星座
 * @param Date|string|integer $date 时间，如果为NULL，得到当前时间,默认为当前时间
 * @return 返回对应的星座
 */
function date_to_star_sign($date=NULL){
	if(!$date && ! is_string($date)) $date=date_to_str($date);
	$date=date_parse($date);	
	$m=$date['month'];$d=$date['day'];
	$star=array('摩羯','宝瓶','双鱼','白羊','金牛','双子','巨蟹','狮子','处女','天秤','天蝎','射手');
 	$zone   = array(1222,122,222,321,421,522,622,722,822,922,1022,1122,1222);
	if((100*$m+$d)>=$zone[0]||(100*$m+$d)<$zone[1])   $i=0;
	else for($i=1;$i<12;$i++){
		if((100*$m+$d)>=$zone[$i]&&(100*$m+$d)<$zone[$i+1])	break;
	}
	return $star[$i].'座';		
}
