<?php
/**
 * 库存同步操作日志
 * @author zwj
 */
require_model('tb/TbModel');

class InvSyncLogModel extends TbModel {
	function __construct(){
		parent::__construct('inv_sync_log', 'log_id');
	}
	
	/*
	 * 根据条件查询数据
	*/
	function get_by_page($filter) {
		$sql_join = "inner join sys_user rr on rl.user_code=rr.user_code";
		$sql_main = "FROM {$this->table} rl ".$sql_join." WHERE 1";
		$sql_values = array();

        if (isset($filter['sync_code']) && $filter['sync_code'] != '') {
            $sql_main .= " AND (rl.sync_code = :sync_code )";
            $sql_values[':sync_code'] = $filter['sync_code'] ;
        }

		//关键字
		if (isset($filter['key_code']) && $filter['key_code'] != '') {
			$sql_main .= " AND (rl.log_info LIKE :key_code )";
			$sql_values[':key_code'] = '%' . $filter['key_code'] . '%';
		}
		//标签类型
		if (isset($filter['type_code']) && $filter['type_code'] != '') {
			$sql_main .= " AND (rl.tab_type = :type_code )";
			$sql_values[':type_code'] = $filter['type_code'] ;
		}
		$select = 'rl.*,rr.user_name,rr.login_type';
		$sql_main .= " order by log_id desc" ;
		//echo $sql_main;
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		
		foreach($data['data'] as &$val){
			if($val['login_type'] == 2) { //操作者为分销用户，改为系统
				$val['user_code'] = '系统';
			} else {
				$val['user_code'] = $val['user_name'];
			}
			if($val['tab_type'] == 'baseinfo'){
                $val['type_name'] = '基本信息';
            } else if($val['tab_type'] == 'shop_ratio'){
                $val['type_name'] = '店铺比例配置';
            } else if($val['tab_type'] == 'goods_ratio'){
                $val['type_name'] = '商品比例配置';
            } else if($val['tab_type'] == 'anti_oversold'){
                $val['type_name'] = '防超卖预警配置';
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
