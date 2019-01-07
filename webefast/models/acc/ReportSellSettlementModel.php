<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);
class ReportSellSettlementModel extends TbModel
{
	/*
	 * 根据条件查询数据
	*/
	function list_data($request){
		$ym_start = date('Y-m',strtotime($request['ym'].'-01')-6*30*24*3600);
		$sql = "select count(*) from report_sell_settlement where shop_code = '{$request['shop_code']}' and account_month_ym='{$request['ym']}'";
		//echo $sql;die;
		$c = $this->db->getOne($sql);
		if ($c == 0){
			return array('html'=>'<h3>没有查找到数据</h3>','lastchanged'=>'----');			
		}
		$sql = "select account_month_ym,ds_je,ys_je,lastchanged FROM report_sell_settlement where shop_code = '{$request['shop_code']}' and account_month_ym>='{$ym_start}' and account_month_ym<= '{$request['ym']}' order by account_month_ym desc";
		$db_arr = $this->db->getAll($sql);
		//echo '<hr/>$db_arr<xmp>'.var_export($db_arr,true).'</xmp>';

		$arr = array();
		$total_ys = 0;
		foreach($db_arr as $sub_db){
				$total_ys += $sub_db['ys_je'];
		}
		$html = "<table class='total_info'><caption>本月已核销的应收金额</caption><tr><td>{$db_arr[0]['ds_je']}</td></tr></table>";
		$html .= "<table class='total_info'><caption>期末应收款（未到账）</caption><tr><td>{$total_ys}</td></tr></table><table class='tbl_list'>";
		foreach($db_arr as $sub_arr){
			$html .= "<tr><td class='td_label'>{$sub_arr['account_month_ym']} 应收款</td><td class='td_cont'>{$sub_arr['ys_je']}</td></tr>";
		}
		$html .= "</table>";
				
		return array('html'=>$html,'lastchanged'=>$db_arr[0]['lastchanged']);
	}
	

}
