<?php
/**
 * 操作日志相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('sys');

class OperateLogModel extends TbModel {
	//业务模块
	public $module = array(array('0'=>'','1'=>'请选择'),array('0'=>'网络订单','1'=>'网络订单'),array('0'=>'进销存','1'=>'进销存'),array('0'=>'商品','1'=>'商品'),array('0'=>'运营','1'=>'运营'),array('0'=>'系统管理','1'=>'系统管理'),array('0'=>'网络店铺','1'=>'网络店铺'),array('0'=>'账务','1'=>'账务'));
	//操作类型
	public $operate_type = array(array('0'=>'','1'=>'请选择'),array('0'=>'菜单','1'=>'菜单'),array('0'=>'新增','1'=>'新增'),array('0'=>'编辑','1'=>'编辑'),array('0'=>'启用','1'=>'启用'),
			               array('0'=>'停用','1'=>'停用'),array('0'=>'删除','1'=>'删除'),array('0'=>'验收','1'=>'验收'),
			               array('0'=>'导入','1'=>'导入'),array('0'=>'导出','1'=>'导出'),array('0'=>'绑定','1'=>'绑定'),array('0'=>'解除绑定','1'=>'解除绑定'),array('0'=>'删除策略','1'=>'删除策略'),array('0'=>'立即执行','1'=>'立即执行'),
			               );
	function __construct(){
		parent::__construct('sys_operate_log', 'operate_log_id');
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
		if (isset($filter['module']) && $filter['module'] != '') {
			$sql_main .= " AND (rl.module = :module )";
			$sql_values[':module'] = $filter['module'];
		}
		//操作类型
		if (isset($filter['operate_type']) && $filter['operate_type'] != '') {
			$sql_main .= " AND (rl.operate_type = :operate_type )";
			$sql_values[':operate_type'] = $filter['operate_type'];
		}
		//商品/单据编码
		if (isset($filter['yw_code']) && $filter['yw_code'] != '') {
			$sql_main .= " AND (rl.yw_code LIKE :yw_code )";
			$sql_values[':yw_code'] = $filter['yw_code'].'%';
		}
                //操作详情
		if (isset($filter['operate_xq']) && $filter['operate_xq'] != '') {
			$sql_main .= " AND (rl.operate_xq LIKE :operate_xq )";
			$sql_values[':operate_xq'] = '%'.$filter['operate_xq'].'%';
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
        
        function get_by_log_page($filter) {
            if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
                $filter[$filter['keyword_type']] = $filter['keyword'];
            }
            if($filter['log_table'] == 1) {
                $log_table = 'api_open_logs';
                $select = "id AS logs_id,type,method,url,add_time,'api_open_logs' AS table_type ";
            } else {
                $log_table = 'api_logs';
                $select = "id AS logs_id,type,method,url,add_time,'api_logs' AS table_type ";
            }
            $sql_values = array();
            $sql_main = " FROM {$log_table} WHERE 1 ";
            // 往前推小时数
            if(isset($filter['front_hour']) && $filter['front_hour'] != '') {
                //记录时间
                $filter['log_add_time_start'] = isset($filter['log_add_time_start']) && $filter['log_add_time_start'] != '' ? $filter['log_add_time_start'] : date('Y-m-d H:i:s');
                $start_time = date('Y-m-d H:i:s',strtotime('-'.$filter['front_hour'].' hour',strtotime($filter['log_add_time_start'])));
                $sql_main .= " AND (add_time >= :start_time) AND (add_time <= :end_time) ";
                $sql_values[':start_time'] = $start_time;
                $sql_values[':end_time'] = $filter['log_add_time_start'];
            }
            // 往后推小时数
            if(isset($filter['behind_hour']) && $filter['behind_hour'] != '') {
                //记录时间
                $filter['log_add_time_start'] = isset($filter['log_add_time_start']) && $filter['log_add_time_start'] != '' ? $filter['log_add_time_start'] : date('Y-m-d H:i:s');
                $end_time = date('Y-m-d H:i:s',strtotime('+'.$filter['behind_hour'].' hour',strtotime($filter['log_add_time_start'])));
                $sql_main .= " AND (add_time >= :start_time) AND (add_time <= :end_time) ";
                $sql_values[':start_time'] = $filter['log_add_time_start'];
                $sql_values[':end_time'] = $end_time;
            }
            //
            if(isset($filter['log_add_time_start']) && $filter['log_add_time_start'] != ''  && empty($filter['behind_hour']) && empty($filter['front_hour'])) {
                $sql_main .= " AND (add_time = :add_time) ";
                $sql_values[':add_time'] = $filter['log_add_time_start'];
            }
            // 关键字
            if(isset($filter['keywords_sle']) && $filter['keywords_sle'] != '') {
                $sql_main .= " AND (params LIKE :params OR post_data LIKE :params2)";
                $sql_values[':params'] = '%' . $filter['keywords_sle'] . '%';
                $sql_values[':params2'] = '%' . $filter['keywords_sle'] . '%';
            }
            $sql_main .= " ORDER BY add_time DESC,id DESC ";
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
//            foreach ($data['data'] as $key => $value) {
//            }
            $ret_status = OP_SUCCESS;
            $ret_data = $data;
            return $this->format_ret($ret_status, $ret_data);
        }
        
        function get_by_log_id($logs_id, $table_type) {
            $sql = "SELECT * FROM {$table_type} WHERE id = :id ";
            $data = $this->db->get_row($sql, array(':id' => $logs_id));
            //是xml还是json，请求参数
            /*$xml_parser = xml_parser_create();  
            if(!xml_parse($xml_parser, $data['params'], true)){   
                $data['php_params'] = json_decode($data['params'], true);
            }else {   // xml 转换为php数组
                $data['php_params'] = (json_decode(json_encode(simplexml_load_string($data['params'])),true));   
            }   
            //返回参数
            if(!xml_parse($xml_parser, $data['return_data'], true)){   
                $data['php_return_data'] = json_decode($data['return_data'], true);
            }else {   // xml 转换为php数组
                $data['php_return_data'] = (json_decode(json_encode(simplexml_load_string($data['return_data'])),true));   
            }   
            
            $data['param_hml'] = $this->get_json_html($data['php_params']);
            $data['return_data_hml'] = $this->get_json_html($data['php_return_data']);*/
            return $data;
        }
        function get_json_html($php_params) {
            $html = '';
            foreach($php_params as $key => $val) {
                $html .= $this->get_html($key, $val);
            }
            return $html;
        }
        function get_html($key, $val) {
            if(is_array($val)) {
                $html = "<span>\"{$key}\"{</span><br>";
                $html .= $this->get_json_html($val);
                $html .= '},<br>';
            } else {
                $html = "<span>\"{$key}\"</span>:<span>\"{$val}\",</span><br>";
            }
            return $html;
        }
}




