<?php
/**
 * 

订单快递适配策略相关业务
 *
 * @author huanghy
 *
 */
require_model('tb/TbModel');
require_lang('crm');
require_lib('util/oms_util', true);

class PolicyExpressRuleModel extends TbModel {
	function get_table() {
		return 'op_policy_express_rule';
	}

	/*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {

		$sql_join = "";
		$sql_main = "FROM {$this->table} ol  left join base_express be  on  ol.express_code = be.express_code  WHERE 1";
		$sql_values = array();
		
	    if (isset($filter['pid']) && $filter['pid'] != '') {
            $sql_main .= " AND pid = :pid ";
            $sql_values[':pid'] = $filter['pid'];    
        }
		
		$select = 'be.*,ol.*';
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}
	
	

	function get_express_by_page($filter) {

		$sql_join = "";
		$sql_main = "FROM base_express  WHERE status = 1";
		$sql_values = array();
		
		$select = 'express_id,express_code,express_name';
		
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);

		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}
	
	
	/**
	 * @param $id
	 * @return array
	 */
	function get_by_pid_code($pid,$code) {
		
		return  $this->get_row(array('pid'=>$pid,'express_code'=>$code));
	}
	

	/**
	 * @param $code
	 * @return array
	 */
	function get_by_code($code) {
		return $this->get_row(array('customer_code'=>$code));
	}
	/**
	 * 通过field_name查询
	 *
	 * @param  $ :查询field_name
	 * @param  $select ：查询返回字段
	 * @return array (status, data, message)
	 */
	public function get_by_field($field_name,$value, $select = "*") {
		$sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
		$data = $this -> db -> get_row($sql, array(":{$field_name}" => $value));
		if ($data) {
			return $this -> format_ret('1', $data);
		} else {
			return $this -> format_ret('-1', '', 'get_data_fail');
		}
	}
	/*
	 * 添加新纪录
	 */
	function insert($express_rule) {
//		$status = $this->valid($express_strategy);
//		if ($status < 1) {
//			return $this->format_ret($status);
//		}
//		
		$ret = $this->get_by_pid_code($express_rule['pid'],$express_rule['express_code']);
//		
		if (empty($ret['data'])) {
//			return $this->format_ret(CUSTOMER_ERROR_UNIQUE_NAME);
                    $r = parent::insert($express_rule);
                    $action_name = '编辑';
                    $data = array(
                        'pid'=>$express_rule['pid'],
                        'action_name'=>$action_name,
                        'desc' => "添加配送方式{$express_rule['express_name']}"
                    );
                    load_model('crm/ExpressStrategyLogModel')->insert($data);
		}
		$ret_status = OP_SUCCESS;
		return $this->format_ret($ret_status);
	}

	//转单时添加会员
	function add_express_strategy($info){
		$status = $this->valid($info);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		$sql = "select customer_code from crm_customer where customer_name = :customer_name";
		$row = CTX()->db->getRow($sql,array('customer_name'=>$info['customer_name']));
		if (!empty($row)) {
                        return $this->format_ret(1,$row);
		} 
		$sql = "select max(customer_id) from crm_customer";
		$max_customer_id = CTX()->db->getOne($sql);
		$info['customer_code'] = (int)$max_customer_id + 1;
		$ret = $this->insert($info);
		$info['customer_id'] = $ret['data'];
		return   $this->format_ret(1,$info);
	}



	/*
	 * 修改纪录
	 */
	function update($policy_express_rule, $policy_express_rule_id,$express_name) {
            $info = $this->get_row(array('policy_express_rule_id'=>$policy_express_rule_id));
            $ret = parent::update($policy_express_rule, array('policy_express_rule_id'=>$policy_express_rule_id));
            $desc = '';
            if ($policy_express_rule['priority'] != $info['data']['priority']) {
                $desc = "{$policy_express_rule['express_name']}的优先级改为{$policy_express_rule['priority']}";
            }
            if ($policy_express_rule['first_weight'] != $info['data']['first_weight']) {
                $desc = "{$policy_express_rule['express_name']}的首重改为{$policy_express_rule['first_weight']}";
            }
            if ($policy_express_rule['first_weight_price'] != $info['data']['first_weight_price']) {
                $desc = "{$policy_express_rule['express_name']}的首重单价改为{$policy_express_rule['first_weight_price']}";
            }
            if ($policy_express_rule['added_weight'] != $info['data']['added_weight']) {
                $desc = "{$policy_express_rule['express_name']}的续重改为{$policy_express_rule['added_weight']}";
            }
            if ($policy_express_rule['added_weight_price'] != $info['data']['added_weight_price']) {
                $desc = "{$policy_express_rule['express_name']}的续重单价改为{$policy_express_rule['added_weight_price']}";
            }
            if ($policy_express_rule['added_weight_type'] != $info['data']['added_weight_type']) {
                if ($policy_express_rule['added_weight_type'] == 'g0') {
                    $desc = "{$policy_express_rule['express_name']}的续重规则改为实重";
                } elseif ($policy_express_rule['added_weight_type'] == 'g1') {
                    $desc = "{$policy_express_rule['express_name']}的续重规则改为半重";
                } else {
                    $desc = "{$policy_express_rule['express_name']}的续重规则改为过重";
                }              
            }
            if (!empty($desc)) {
                $action_name = '编辑';
                $data = array(
                    'pid'=>$policy_express_rule['pid'],
                    'action_name'=>$action_name,
                    'desc' => $desc
                );
                load_model('crm/ExpressStrategyLogModel')->insert($data);
            }
            return $ret;
	}

	
	/*
	 * 服务器端验证
	 */
	private function valid($data, $is_edit = false) {
		if (!isset($data['customer_name']) || !valid_input($data['customer_name'], 'required')) return CUSTOMER_ERROR_NAME;
		return 1;
	}
	
	function is_exists($value, $field_name='express_code') {
		$ret = parent::get_row(array($field_name=>$value));
		return $ret;
	}
	
	function get_express_list($policy_express_id){
		$sql = "select policy_express_rule_id,pid,express_code,priority  from {$this->table}  where pid = ".$policy_express_id." ";
		$data = CTX()->db->getAll($sql);
		return $data;
	}


	//修改地址信息
	function update_customer_address($info,$wh){
		//echo '<hr/>info<xmp>'.var_export($info,true).'</xmp>';
		//echo '<hr/>wh<xmp>'.var_export($wh,true).'</xmp>';
        $ret = M('crm_customer_address')->update($info,$wh);
        return $ret;
	}

	//删除地址信息
	function delete_express($request){
            $ret = parent :: delete(array('policy_express_rule_id' => $request['policy_express_rule_id']));
            $action_name = '编辑';
            $data = array(
                'pid'=>$request['pid'],
                'action_name'=>$action_name,
                'desc' => "删除配送方式{$request['express_name']}"
            );
            load_model('crm/ExpressStrategyLogModel')->insert($data);
            return $ret;
	}

    /**
    * 删除快递策略时  同时删除快递规则
    */
    function delete($condition) {
        $ret = parent :: delete($condition);
        return $ret;
    }
    
    function update_active($active, $policy_express_id) {
        if (!in_array($active, array(1, 2))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('status' => $active), array('policy_express_id' => $policy_express_id));
        return $ret;
    }
    /**
     * 根据地区 配送方式获取策略及配送方式信息
     * @param $district
     * @return array|bool
     */
    public function get_by_express_and_district($district,$express_code,$is_city=0){
    	$sql = "select a.*, c.* from op_policy_express a
        inner join op_policy_express_area b on b.pid = a.policy_express_id
        inner join op_policy_express_rule c on c.pid = a.policy_express_id
        where b.area_id = :area_id and c.express_code=:express_code
        order by c.priority desc";
        $area_city_arr = array('442000000000', '441900000000');
        // 441900000000:东莞   442000000000:中山市
        if ($is_city == 0 || in_array($district, $area_city_arr)){
            return $this->db->get_row($sql, array('area_id'=>$district,'express_code'=> $express_code));
        } 
        //若传市
        $area_id_arr = $this->get_district_by_city($district);
        $area_where = " b.area_id='" . implode("' OR b.area_id='", $area_id_arr) . "'";
        $sql = "select a.*, c.* from op_policy_express a
        inner join op_policy_express_area b on b.pid = a.policy_express_id
        inner join op_policy_express_rule c on c.pid = a.policy_express_id
        where ({$area_where}) and c.express_code=:express_code
        order by c.priority desc";
        $data = $this->db->get_all($sql, array('express_code'=> $express_code));
            
        $area_num = count($area_id_arr);
        if (count($data) == $area_num){
            return $data[0];
        }
        return array();
        
    	
    }
    private function get_district_by_city($city_id) {
        $sql = "select id from base_area where  parent_id = :parent_id   ";
        $data = $this->db->get_all($sql, array(':parent_id' => $city_id));
        $area_data = array();
        foreach ($data as &$val) {
            $area_data[] = $val['id'];
        }
        return $area_data;
    }

}


