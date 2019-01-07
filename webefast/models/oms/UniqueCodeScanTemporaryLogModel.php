<?php
require_model('tb/TbModel');
require_lib('util/oms_util', true);

class UniqueCodeScanTemporaryLogModel extends TbModel {
	function get_table() {
		return 'unique_code_scan_temporary_log';
	}
	
	function get_unique_code_by_record_code($sell_record_code){
		$sql = "select unique_code from {$this->table} where sell_record_code =:sell_record_code";
		$ret = $this->db->get_all($sql,array(':sell_record_code' => $sell_record_code));
		$unique_code = array();
		if (!empty($ret)){
			foreach ($ret as $r){
				$unique_code[] = $r['unique_code'];
			}
		}
		return $unique_code;
	}
}