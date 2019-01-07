<?php
/**
 * 登录日志相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('sys');

class LoginLogModel extends TbModel {
	//是否登录
	public $type = array(array('0'=>'','1'=>'请选择'),array('0'=>'0','1'=>'登录'),array('0'=>'1','1'=>'退出'));
	function __construct(){
		parent::__construct('sys_login_log', 'login_log_id');
	}

	/*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {
		
		$sql_join = "";
		$sql_main = "FROM {$this->table} rl LEFT JOIN sys_user r2 on rl.user_code = r2.user_code WHERE 1";
		$sql_values = array();
		//登录名
		if (isset($filter['user_code']) && $filter['user_code'] != '') {
			$sql_main .= " AND (rl.user_code LIKE :user_code )";
			$sql_values[':user_code'] = $filter['user_code'].'%';
		}
		//真实姓名
		if (isset($filter['user_name']) && $filter['user_name'] != '') {
			$sql_main .= " AND (r2.user_name LIKE :user_name )";
			$sql_values[':user_name'] = $filter['user_name'].'%';
		}
		//IP地址
		if (isset($filter['ip']) && $filter['ip'] != '') {
			$sql_main .= " AND (rl.ip LIKE :ip )";
			$sql_values[':ip'] = $filter['ip'].'%';
		}
		//业务模块
		if (isset($filter['type']) && $filter['type'] != '') {
			$sql_main .= " AND (rl.type = :type )";
			$sql_values[':type'] = $filter['type'];
		}
		//时间
		if (isset($filter['add_time_start']) && $filter['add_time_start'] != '') {
			$sql_main .= " AND (rl.add_time >= :add_time_start )";
			$sql_values[':add_time_start'] = $filter['add_time_start'];
		}
		
		if (isset($filter['add_time_end']) && $filter['add_time_end'] != '') {
			$sql_main .= " AND (rl.add_time < :add_time_end )";
			$sql_values[':add_time_end'] = $filter['add_time_end'];		
		}
		
		$select = 'rl.*,r2.user_name';
		$sql_main .= " order by add_time desc ";
		//$data =  $this->get_page_from_sql($filter, $sql_main, $select);
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		foreach($data['data'] as $key => $value){
			$arr_type = $this -> type;
			foreach($arr_type as $v){
							
				if(isset($v['0']) && $v['0'] != '' && $v['0'] == $value['type']){
					$data['data'][$key]['type'] = $v['1'];
					//echo "dong";
				}
			}
		}
		
		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);
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
	 * 添加日志
	 */
	function insert($data) {
		return parent::insert($data);
	}
	
	/*
	 * 删除记录
	 * */
	function delete($range) {
		switch($range){
			case '2'://一天前
				$to_day = date("Y-m-d",strtotime("-1 day"));
				$where = "add_time <= '{$to_day}' ";
				break;
			case '3'://一周前
				$to_day = date("Y-m-d",strtotime("-7 day"));
				$where = "add_time <= '{$to_day}' ";
				break;
			case '4'://一月前
				$to_day = date("Y-m-d",strtotime("-30 day"));
				$where = "add_time <= '{$to_day}' ";
				break;
			default:
				$where = 1;
		}
		$sql = "delete from {$this->table} where {$where} ";
		$data = $this -> db -> query($sql);
		if ($data) {
			return $this -> format_ret("1", $data, 'delete_success');
		} else {
			return $this -> format_ret("-1", '', 'delete_error');
		}
		
	}
	
	
	
}



