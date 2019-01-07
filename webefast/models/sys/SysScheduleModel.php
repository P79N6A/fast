<?php
/**
 *自动服务
 *
 * @author wq
 */
require_model('tb/TbModel');
require_lib('apiclient/Validation', true);
class SysScheduleModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent::__construct($table);
    }
    private $api_url = "http://121.41.163.99/fastapp/webapi/web/?app_act=api/schedule/save";
    function get_table() {
        return 'sys_schedule';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
 
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
        // 任务类型
        if (isset($filter['class_code']) && $filter['class_code'] != '') {
            $sql_main .= " AND class_code = :tpl_name ";
            $sql_values[':class_code'] = $filter['class_code'] ;
        }
        // 销售平台
        if (isset($filter['sale_channel_id']) && $filter['sale_channel_id'] != '') {
            $sql_main .= " AND sale_channel_id = :sale_channel_id ";
            $sql_values[':sale_channel_id'] = $filter['sale_channel_id'];
        }
        // 状态
        if (isset($filter['status']) && $filter['status'] != '') {;
            $sql_main .= " AND status = :status ";
            $sql_values[':status'] = $filter['status'];
        }
     
        $select = '*';
    
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        $arr_channel =array( '9'=>'淘宝','13'=>'京东','16'=>'一号店','10'=>'拍拍','14'=>'亚马逊');
        
        foreach($ret_data['data'] as $k=>$row){
            $row['sale_channel_name'] = $arr_channel[$row['sale_channel_id']];
            $row['status_name'] = ($row['status']==0)?'未启用':'已启用';
            $ret_data['data'][$k] = $row;
        }
 
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 添加新纪录
     */
    function insert($sms_tpl) {
        $status = $this->valid($sms_tpl);
        if ($status < 1) {
            return $this->format_ret($status);
        }

//        $ret = $this->is_exists($sms_tpl['user_code']);
//        if ($ret['status'] > 0 && !empty($ret['data'])) {
//            return $this->format_ret('sms_tpl_error_unique_code');
//        }
//
//        $ret = $this->is_exists($sms_tpl['user_name'], 'user_name');
//        if ($ret['status'] > 0 && !empty($ret['data'])) {
//            return $this->format_ret('sms_tpl_error_unique_code');
//        }

        return parent::insert($sms_tpl);
    }

    function update_status($id,$status) {
        if (!in_array($status, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        
//        $api_status = $this->set_api_schedule($id,$status);
//        if($api_status!==true){
//            return $this->format_ret(-1,'',  implode(",", $api_status));
//        }
        
        $ret = parent::update(array('status' => $status), array('id' => $id));
        $schedule_data = $this->get_row(array('id'=>$id));
        //插入系统操作日志
        $log = array('user_id' => CTX()->get_session('user_id'),
                'user_code' => CTX()->get_session('user_code'),
                'ip' => $_SERVER["REMOTE_ADDR"],
                'add_time' => date('Y-m-d H:i:s'),
                'module' => '自动服务',
                'yw_code' => '',
                'operate_type' => $status == 1?'开启':'关闭',
                'operate_xq' => $schedule_data['data']['name']
            );
        load_model('sys/OperateLogModel')->insert($log);
        return $ret;
    }
    
    function set_plan_exec_data($code,$plan_exec_data){
        //plan_exec_time
        $data['plan_exec_time'] =  $this->get_plan_exec_time($plan_exec_data);
        $data['plan_exec_data'] = json_encode($plan_exec_data);
        return $this->update($data, array('code'=>$code));
        
             
    }
    private function get_plan_exec_time($plan_exec_data){
        $time = time();
        $now_date = date('Y-m-d');
        $time_arr = array();
        foreach($plan_exec_data as $key=>$exec_data ){
            foreach($exec_data as $exec_time){
                if($key=='time'){
                    $e_time = strtotime($now_date.$exec_time);
                    $time_arr[] =  $e_time;
                }else{
                     $e_time = strtotime($exec_time);
                     $time_arr[] =  $e_time;
                }
            }
        }
        $time_arr[] = $time;
         sort($time_arr);
         $key = array_search($time, $time_arr);
         $key = $key+1;
         if($key==(count($time_arr))){
             $key = 0;
         }
        return  $time_arr[$key];
    }
            
    
    
    
    function get_schedule_shop($sys_schedule_id) {
        $sale_channel_id  = $this->db->getOne("select sale_channel_id from sys_schedule where id=:id", array(":id"=>$sys_schedule_id));
         if (empty($sale_channel_id)) {
            return $this->format_ret('error_params');
        }

        $sql = " select bd.shop_id,bd.shop_name,sd.status from  base_shop  bd
            left join  sys_schedule_shop  sd ON bd.shop_id=sd.shop_id
            where is_active=:is_active and sale_channel_id = :sale_channel_id";
          $sql_values[':is_active'] = 1;
          $sql_values[':sale_channel_id'] = $sale_channel_id;


        $shop_data = $this->db->get_all($sql,$sql_values);
        
        


        
        $ret_status = OP_SUCCESS;
        $ret_data = $shop_data;
        return $this->format_ret($ret_status, $ret_data);
    }
    function set_schedule_shop($sys_schedule_id,$shop_id,$status) {
      if($status==0){
          $where = array('sys_schedule_id'=>$sys_schedule_id,'shop_id'=>$shop_id);
          $data = $this -> db -> create_mapper('sys_schedule_shop') -> delete($where);
      }else{
          $schedule_data = array('sys_schedule_id'=>$sys_schedule_id,'shop_id'=>$shop_id);
          $data = $this -> db -> create_mapper('sys_schedule_shop') ->insert($schedule_data);
      }
 
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    //废弃掉api
    function set_api_schedule($sys_schedule_id,$switch){
        $ret = $this->get_row(array('id'=>$sys_schedule_id));//type
        if($ret['data']['type']!=1){
            return TRUE;
        }
        if(!empty ($ret['data']['request'])){
            $request= json_decode($ret['data']['request'],TRUE);
        }
        //$kh_id =  CTX()->get_session('kh_id'); //'1094';
          $kh_id = CTX()->saas->get_saas_key();
        //$kh_id =  318;
        $api_type = array('type'=>'taobao');
        $request['switch'] = $switch;
        $error_arr = array();
        $status = array(
        -2 => 'URL请求不合法',
        -1 => '程序异常',
        0 => '无需操作',
        1 => '添加任务成功',
        2 => '设置成功',
        3 => '查询成功'
        );
        $api_url_conf = require_conf('api_url');
        $api_url = $api_url_conf['api'].'api/schedule/save';
      
        foreach($api_type as $type){
            $restult = Validation::send($api_url, $kh_id, 'efast5', $type, $request);
            $ret = json_decode($restult['response'], true);
              if($ret['status']<0){
              $error_arr[] = $status[$ret['status']];
            }
        }
        if(!empty($error_arr)){
            return $error_arr;
        }
        return TRUE;
    }

    public function open_close_service($params)
    {
    	foreach ($params as $key => $param){
    		$ret = parent::update(array('status' => $param), array('code' => $key));
    	}
    	return $ret;
    }
    function execute_right_off_log($code){
        $data = $this->get_row(array('code'=>$code));
        $log = array('user_id' => CTX()->get_session('user_id'),
                'user_code' => CTX()->get_session('user_code'),
                'ip' => $_SERVER["REMOTE_ADDR"],
                'add_time' => date('Y-m-d H:i:s'),
                'module' => '自动服务',
                'yw_code' => '',
                'operate_type' => '立即执行',
                'operate_xq' => $data['data']['name']
            );
        load_model('sys/OperateLogModel')->insert($log);
    }
}
