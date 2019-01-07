<?php
/**
* 自动服务相关业务
*
* @author dfr
*/
require_model('tb/TbModel');

class ScheduleModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'sys_schedule';
    }


    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
        if (isset($filter['type']) && $filter['type'] != '') {
            $sql_main .= " AND type = :type";
            $sql_values[':type'] = $filter['type'];
        }
        //校验增值服务
        $check = load_model('common/ServiceModel')->check_is_auth_by_value('DAOXUN_ERP_UPLOAD');
        if ($check != true) {
            $sql_main .= " AND code<>'cli_daoxun_upload' ";
        }
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
    	foreach($data['data'] as $k=>$sub_data){
    		$add_service_status = load_model('common/ServiceModel')->check_is_auth_by_value('alipay');
                $alipay_cli = array('alipay_download_cmd','alipay_accounts_cmd');
    		if ($add_service_status == false && in_array($sub_data['code'],$alipay_cli)){
    			unset($data['data'][$k]);                        
    			continue;
    		}
                //通灵增值服务，库存差异对比
                $control_compare_tl = load_model('common/ServiceModel')->check_is_auth_by_value('print_warranty');//和质保书一个增值服务
                //如果没有开启权限，并且code为差异，不显示库存差异对比的参数列
                if($control_compare_tl == false && $sub_data['code'] == 'inventory_control_compare'){
                    unset($data['data'][$k]); 
                    continue;
                }
    		if($sub_data['status'] == 1){
    		$data['data'][$k]['status_html'] = "<button class='status_btn' style ='background:#1695ca; color:#FFF; border-color:#1695ca;'>已启用</button><button class='status_btn' onclick = changeType(".$sub_data['id'].",0)>未启用</button>";
    		}
    		if($sub_data['status'] == 0){
    		$data['data'][$k]['status_html'] = "<button class='status_btn' onclick = changeType(".$sub_data['id'].",1)>已启用</button><button class='status_btn' style ='background:#1695ca; color:#FFF; border-color:#1695ca;' >未启用</button>";
    		}

            if ($filter['type'] == 0 || $filter['type'] == 2 || $filter['type'] == 6 || $sub_data['code'] == 'cli_erp3_o2o_record_info') {
    			$data['data'][$k]['status_html'] .= "<button class='status_btn' id ='".$sub_data['code']."_id' onclick = execute_right_off('".$sub_data['code']."','".$sub_data['code']."_id')>立即执行</button>";
                } elseif($filter['type'] == 3){
                    $erp_cli = array('erp_item_download_cmd','erp_item_inv_update_cmd','erp_barcode_download_cmd');
                    if (in_array($sub_data['code'], $erp_cli)){
                        $data['data'][$k]['status_html'] .= "<button class='status_btn' id ='".$sub_data['code']."_id' onclick = execute_right_off_api('".$sub_data['code']."','".$sub_data['code']."_id')>立即执行</button>";
                    }
                    
                }
    		$data['data'][$k]['last_time'] = (isset($sub_data['last_time']) && $sub_data['last_time'] > 0)? date("Y-m-d H:i:s",$sub_data['last_time']) : '';
    		if ($sub_data['loop_time'] && $sub_data['loop_time'] > 0 && $data['data'][$k]['last_time'] > 0) {
                        if(!empty($sub_data['plan_exec_time']) && ($sub_data['plan_exec_time'] > $sub_data['last_time']) && ($sub_data['plan_exec_time'] > time())){
                            $next_execute_time = $sub_data['plan_exec_time'];
                        }else{
                            $next_execute_time = $sub_data['last_time'] + $sub_data['loop_time'];
                        }

    			$data['data'][$k]['next_execute_time'] = date("Y-m-d H:i:s",$next_execute_time);
    		} else {
    			$data['data'][$k]['next_execute_time'] = '';
    		}
            $data['data'][$k]['loop_time'] = round($sub_data['loop_time']/60,2).'分钟';
        	$data['data'][$k]['plan_exec_time'] = (isset($sub_data['plan_exec_time']) && $sub_data['plan_exec_time'] > 0)? date("Y-m-d H:i:s",$sub_data['plan_exec_time']) : '';
		}
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    //执行efast_api定时器
    function execute_schedule_api($task_code){
        $conf = require_conf('sys/api_schedule');
        $erp_cli = array('erp_item_download_cmd','erp_item_inv_update_cmd','erp_barcode_download_cmd');
        $service = $conf[$task_code];
        $params = array();
        if(in_array($task_code, $erp_cli)){
            $sql = "select erp_config_id,erp_system from erp_config order by erp_config_id desc ";
            $erp_conf = $this->db->get_row($sql);
            //3000j
            if ($erp_conf['erp_system'] == 1){
                $fun = $service['bs3000j'];
            } else {
                //erp
                $fun = $service['bserp'];
            }
            $params = array('erp_config_id'=>$erp_conf['erp_config_id']);
        }
        if(!empty($fun)){
            load_model('sys/EfastApiModel')->request_api($fun, $params);
        }

        return $this->format_ret(1,'');
        
    }
    function get_by_id($id) {
        $arr = $this->get_row(array('id' => $id));
        return $arr;
    }

    
    
    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('status' => $active), array('id' => $id));
        return $ret;
    }


    function get_val_by_code($code) {
        $code_arr = is_array($code) ? $code : array($code);
        $sql_value = array();
        $code_str = $this->arr_to_in_sql_value($code_arr, 'code', $sql_value);
        $sql = "SELECT code,name,status FROM {$this->table} WHERE code IN({$code_str})";
        $result = $this->db->get_all($sql, $sql_value);
        $code_info = load_model('util/ViewUtilModel')->get_map_arr($result, 'code');
        return $code_info;
    }

}