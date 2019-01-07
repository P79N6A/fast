<?php

require_model('tb/TbModel');
require_lang('prm');

class ApiGoodsEexportModel extends TbModel {
	function get_table() {
		return 'api_goods';
	}
	function export($filter){
		$sql_join = "";
		$sql_main = "FROM {$this->table} rl
		LEFT JOIN api_goods_sku r2 on rl.goods_from_id = r2.goods_from_id
		
		WHERE r2.source = 'taobao'";
		 
		$sql_values = array();
	
			
		//店铺
		if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
					$arr = explode(',',$filter['shop_code']);
					$str = "'".join("','",$arr)."'";
					
					$sql_main .= " AND rl.shop_code in ({$str}) ";
		}
		$select = 'select rl.goods_name,rl.goods_code,rl.goods_from_id,r2.price,r2.goods_barcode ,r2.source,r2.sku_properties_name ';
		$sql = $select.$sql_main ;
		//echo $sql;
	    $data_sku = $this->db->get_all($sql);
	    //print_r($data_sku);
	    return $data_sku;
	}
}