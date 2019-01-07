<?php
require_lib ( 'util/web_util', true );
class sys_log {
	//删除时间段
	public $range = array(array('0'=>'1','1'=>'全部'),array('0'=>'2','1'=>'一天前'),array('0'=>'3','1'=>'一周前'),array('0'=>'4','1'=>'一月前'));
	
	function do_list(array & $request, array & $response, array & $app) {
		 //是否登录
		 $response['type'] = load_model('sys/LoginLogModel')->type;
		 //业务模块
		 $response['module'] = load_model('sys/OperateLogModel')->module;
	     //操作类型
		 $response['operate_type'] = load_model('sys/OperateLogModel')->operate_type;
	}
	
	function do_add(array & $request, array & $response, array & $app) {
		//添加操作日志start
		$module = '商品'; //模块名称
		$yw_code = 'sssqqq'; //业务编码
		$operate_xq = '商品确认成功';//操作详情
		$log = array('user_id'=>CTX()->get_session('user_id'),'user_code'=>CTX()->get_session('user_code'),'ip'=>get_client_ip(),'add_time'=>date('Y-m-d H:i:s'),'module'=>$module,'yw_code'=>$yw_code,'operate_xq'=>$operate_xq);
		$ret1 = load_model('sys/OperateLogModel')->insert($log);
		//添加操作日志end
		exit;
	}
	
	function delete(array & $request, array & $response, array & $app){
		$response['range'] = $this->range;
	}
	
	function do_delete(array & $request, array & $response, array & $app) {
		
		if(isset($request['range']) && $request['range'] <> ''){
			$ret = load_model('sys/OperateLogModel')->delete($request['range']);
			exit_json_response($ret);
		}
	}
        function do_view_log(array & $request, array & $response, array & $app) {
            $response['data'] = load_model('sys/OperateLogModel')->get_by_log_id($request['logs_id'], $request['table_type']);
//            $response['data']['php_params'] = json_decode($response['data']['params'], true);
//            $response['data']['php_return_data'] = json_decode($response['data']['return_data'], true);
        }
	
}
