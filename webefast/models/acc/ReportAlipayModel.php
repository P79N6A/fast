<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);
class ReportAlipayModel extends TbModel
{
	/*
	 * 根据条件查询数据
	*/
	function list_data($request){
		$sql = "select account_item,is_account_in,je,lastchanged FROM report_alipay where shop_code = '{$request['shop_code']}' and account_month_ym='{$request['ym']}'";
		$db_arr = $this->db->getAll($sql);
		if (empty($db_arr)){
			return array('html1'=>'<h3>没有查找到数据</h3>','html2'=>'','html3'=>'','lastchanged'=>'----');
		}
		$arr = array();
		$total_arr = array('in'=>0,'out'=>0);
		foreach($db_arr as $sub_db){
			if ($sub_db['is_account_in'] == 1){
				$total_arr['in'] += $sub_db['je'];
			}
			if ($sub_db['is_account_in'] == 2){
				$total_arr['out'] += $sub_db['je'];
			}			
			$arr[$sub_db['is_account_in']][] = $sub_db;
		}
		$html1 = "<table class='total_info'><caption>支付宝收入（交易相关）</caption><tr><td>{$total_arr['in']}</td></tr></table><table class='tbl_list'>";
		foreach($arr[1] as $sub_arr){
			$html1 .= "<tr><td class='td_label'>{$sub_arr['account_item']}</td><td class='td_cont'>{$sub_arr['je']}</td></tr>";
		}
		$html1 .= "</table>";
		//------------------------
		$html2 = "<table class='total_info'><caption>支付宝支出（交易相关）</caption><tr><td>{$total_arr['out']}</td></tr></table><table class='tbl_list'>";
		foreach($arr[2] as $sub_arr){
			$html2 .= "<tr><td class='td_label'>{$sub_arr['account_item']}</td><td class='td_cont'>{$sub_arr['je']}</td></tr>";
		}
		$html2 .= "</table>";
		//------------------------
		$html3 = "<table class='tbl_list'><caption>交易收款核销分解</caption>";
		foreach($arr[3] as $sub_arr){
			$html3 .= "<tr><td class='td_label'>{$sub_arr['account_item']}</td><td class='td_cont'>{$sub_arr['je']}</td></tr>";
		}
		$html3 .= "</table>";
				
		return array('html1'=>$html1,'html2'=>$html2,'html3'=>$html3,'lastchanged'=>$db_arr[0]['lastchanged']);
	}
	

}
