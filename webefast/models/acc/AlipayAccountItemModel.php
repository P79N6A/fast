<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);
class AlipayAccountItemModel extends TbModel
{
	/*
	 * 根据条件查询数据
	*/
	function list_data($request){
		$sql = "select code,account_item,in_out_flag,lastchanged from alipay_account_item where in_out_flag = '{$request['in_out_flag']}'";
		$db_arr = $this->db->getAll($sql);

		$html = "<table class='tbl_list'><tr><th>科目代码</th><th>科目名称</th><th>是否系统内置</th><th>创建时间</th></tr>";
		foreach($db_arr as $sub_arr){
			$html .= "<tr><td>{$sub_arr['code']}</td><td>{$sub_arr['account_item']}</td><td>是</td><td>{$sub_arr['lastchanged']}</td></tr>";
		}
		$html .= "</table>";

		return $html;
	}

}
