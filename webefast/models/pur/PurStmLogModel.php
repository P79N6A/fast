<?php
/**
 * 进销存日志
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('pur');

class PurStmLogModel extends TbModel {
	function __construct(){
		parent::__construct('pur_stm_log', 'pur_stm_log_id');
	}
	/*
	 * 根据条件查询数据
	*/
	
	function get_by_page($filter) {
		$sql_join = "left join sys_user rr on rl.user_code=rr.user_code";
		$sql_main = "FROM {$this->table} rl ".$sql_join." WHERE 1";
		$sql_values = array();
		//单据编号
		if (isset($filter['pid']) && $filter['pid'] != '') {
			$sql_main .= " AND (rl.pid = :pid )";
			$sql_values[':pid'] = $filter['pid'] ;
		}
		//模块
		if (isset($filter['module']) && $filter['module'] != '') {
			$sql_main .= " AND (rl.module = :module )";
			$sql_values[':module'] = $filter['module'] ;
		}
		$select = 'rl.*,rr.user_name,rr.login_type';
		$sql_main .= " order by  pur_stm_log_id desc" ;
		//echo $sql_main;
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);

		/*
		filter_fk_name($data['data'], array('store_code|store', 'adjust_type|record_type'));
		*/ 
                $user_code_arr = array();
                if(!empty($data['data'])){
                   $id_val = array_column($data['data'],'pur_stm_log_id');
                    $sql_val = [];
                    $log_str = $this->arr_to_in_sql_value($id_val, 'pur_stm_log_id', $sql_val);
                    $sql = "select user_code,pur_stm_log_id from pur_stm_log where pur_stm_log_id in ({$log_str})";
                    $list = $this->db->getAll($sql, $sql_val);
                    $user_code_arr = array_column($list,'user_code','pur_stm_log_id'); 
                }
                
                foreach($data['data'] as &$val){
                    if($filter['module'] == 'wbm_notice_record' && $val['login_type'] == 2) { //操作者为分销用户，改为系统
                        $val['user_code'] = '系统';
                    } else {
                        $val['user_code'] = $val['user_name'];
                    }
                    if(empty($val['user_name'])){
                      $val['user_code'] = $user_code_arr[$val['pur_stm_log_id']];
                    }

                }
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}
	/*
	 * 添加日志
	*/
	function insert($data) {
		return parent::insert($data);
	}
    
    function insert_multi($row_arr, $is_filter_repeat = false) {
        return parent::insert_multi($row_arr, $is_filter_repeat);
    }
}
