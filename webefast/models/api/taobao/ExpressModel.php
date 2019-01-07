<?php
require_model('tb/TbModel');
class ExpressModel extends TbModel {
	function get_by_page($filter = array()) {
		$sql_values = array();
		$sql_join = "";
		$sql_main = "FROM api_express tl WHERE 1";
		$select = 'tl.*';
		
		if(isset($filter['keyword'])){
		    
		}
		
		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

		$ret_status = OP_SUCCESS;

		return $this->format_ret($ret_status, $data);
	}
}
