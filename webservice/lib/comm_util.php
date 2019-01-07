<?php
/**
 * 快速产生单据编号
 *
 * @access  public
 * @param   string   $bill_type   单据类型
 * @param   int      $length      流水号长度
 * @return  string   返回单据编号
 */
function create_fast_bill_sn($bill_type, $length=4){
	static $arr_type  =  array('WTTD', 'XQTD','DGBH','ZZDGBH','XQTD');
	if(!in_array($bill_type, $arr_type)) return false;
	
	$prefix='';
	switch ($bill_type) {
		default:
			return false;
			break;
		case 'WTTD':
			$prefix = 'WTTD';
			break;
		case 'XQTD':
			$prefix = 'XQTD';
			break;
                case 'DGBH':
			$prefix = 'DGBH';
			break;  
                case 'ZZDGBH':
			$prefix = 'ZZDGBH';
			break; 
                case 'XQTD':
			$prefix = 'XQTD';
			break; 
	}
	return create_bill_sn($bill_type, $prefix.date('Ymd'), $length);
}
/**
 * 单据编号生成
 *
 * @access  public
 * @param   string   $bill_type   单据类型
 * @param   string   $prefix      单据前缀符
 * @param   int      $length      流水号长度
 * @return  string   返回单据编号
 */
function create_bill_sn($bill_type, $prefix='', $length=4)
{
		$sn = empty($prefix) ? generate_rand($length) : ($prefix . generate_rand($length));	
		return $sn;
}
/**
 * 随机流水号生成
 *
 * @access  public
 * @param   int   $length 随机编号长度
 * @return  string
 */
function generate_rand($length = 4)
{
	$chars = '0123456789';

	for ($i = 0, $count = strlen($chars); $i < $count; $i++)
	{
	$arr[$i] = $chars[$i];
	}

	mt_srand((double) microtime() * 1000000);
	shuffle($arr);
	return substr(implode('', $arr), 0, $length);
}

function not_null(& $key) {
	if (is_array($key)) return TRUE;
	return (isset($key) && '' !== trim($key) && !is_null($key));
}