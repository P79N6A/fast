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

class ExpressStrategyLogModel extends TbModel {
    function get_table() {
        return 'op_policy_express_log';
    }

    /*
     * 根据条件查询数据
     */
    function get_by_page($filter) {

       	$sql_join = "";
		$sql_main = "FROM {$this->table} ol WHERE 1";
		$sql_values = array();
		
//	    if (isset($filter['pid']) && $filter['pid'] != '') {
            $sql_main .= " AND pid = :pid ";
            $sql_values[':pid'] = $filter['pid'];    
//        }
		
		$select = 'ol.*';
                $sql_main .= 'ORDER BY add_time DESC';
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
    }


    function insert($data) {
    	$data['add_time'] = date('Y-m-d H:i:s');
    	$data['user_code'] =  CTX()->get_session('user_code');
    	$data['user_id'] =  CTX()->get_session('user_id');
    	return parent::insert($data);
    }
    
}


