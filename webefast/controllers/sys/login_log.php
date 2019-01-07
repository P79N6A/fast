<?php
require_lib ( 'util/web_util', true );
class login_log {
	//删除时间段
	public $range = array(array('0'=>'1','1'=>'全部'),array('0'=>'2','1'=>'一天前'),array('0'=>'3','1'=>'一周前'),array('0'=>'4','1'=>'一月前'));
	function do_list(array & $request, array & $response, array & $app) {
		//是否登录
		 $response['type'] = load_model('sys/LoginLogModel')->type;
	}
	
	function do_add(array & $request, array & $response, array & $app) {
		
	}

	function delete(array & $request, array & $response, array & $app){
		$response['range'] = $this->range;
	}
	function do_delete(array & $request, array & $response, array & $app) {
		if(isset($request['range']) && $request['range'] <> ''){
			$ret = load_model('sys/LoginLogModel')->delete($request['range']);
			exit_json_response($ret);
		}
	}
	
}


