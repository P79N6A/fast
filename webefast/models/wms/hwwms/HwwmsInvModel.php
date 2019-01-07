<?php

require_model("wms/WmsInvModel");
class HwwmsInvModel extends WmsInvModel {
	function __construct()
	{
		parent::__construct();
	}

	function inv_search($efast_store_code,$barcode_arr){
		$ret = array();
		return $ret;
	}

	function sync_inv_incr($efast_store_code,$start,$end){
		return array();
		//return $ret;		
	}
	
}