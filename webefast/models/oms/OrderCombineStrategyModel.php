<?php
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class OrderCombineStrategyModel extends TbModel {
	public function __construct($table = '', $db = '') {
		$table = $this->get_table();
		parent :: __construct($table);
	}
	
	function get_table() {
		return 'order_combine_strategy';
	}
	
	function get_by_page($filter) {
		$sql_values = array();
		$sql_main = "FROM {$this->table} WHERE 1";
		$select = '*';
		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
		foreach($data['data'] as $k=> &$value){
                        $rule_status_value_check = ($value['rule_status_value'] == 1)?'checked="true"':'';
                        $rule_scene_value_check =  ($value['rule_scene_value'] == 1)?'checked="true"':'';

                        $value['rule_value_html'] = '<input type="checkbox"  name="'.$value['id'].'" class="rule_status_value"  '.$rule_status_value_check.'  />手工合并&nbsp;&nbsp;&nbsp;';
                        $value['rule_value_html'] .= '<input type="checkbox"  name="'.$value['id'].'" class="rule_scene_value"   '.$rule_scene_value_check.'  />自动合并';
			
		}
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}
	
	public function update_status($id, $value, $type) {
		if (!in_array($value, array(0, 1))) {
			return $this->format_ret('error_params');
		}
		$ret = parent::update(array($type => $value), array('id' => $id));
		return $ret;
	}
	
	public function get_val_by_code($rule_code){
                if(is_array($rule_code)){
                    $rule_code = implode("','", $rule_code);
                }
            
		$sql = "select rule_code,rule_status_value,rule_scene_value from order_combine_strategy where rule_code in ('{$rule_code}')";
		$data = $this->db->get_all($sql);
                $ret_data = array();
                foreach($data as  $row){
                    $ret_data[$row['rule_code']] = $row;
                }
                
		return $ret_data;
	}
  
}
