<?php
require_lib ('util/web_util', true);
class sms_queue {
    function do_list(array &$request, array &$response, array &$app) {

    }
    function detail(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/SmsQueueModel')->query_by_id($request['_id']);
        $sms_conf = require_conf('sys/sms');
        $ret['data']['status_exp'] = $sms_conf['status'][$ret['data']['status']];
        $response['data'] = $ret['data'];
    }

// 	function batchSend(array &$request, array &$response, array &$app) {
//
//    }
    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/SmsQueueModel')->delete($request['id']);
        exit_json_response($ret);
    }


	function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('sys/SmsQueueModel')->update_active($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }
    
    
	function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('tpl_type', 'tpl_name', 'is_active', 'sms_info', 'remark'));
        $ret = load_model('sys/SmsQueueModel')->insert($data, $request['id']);
        exit_json_response($ret);
    }
    
    
	function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('tpl_name', 'is_active', 'sms_info', 'remark'));
        
        $ret = load_model('sys/SmsQueueModel')->update($data, $request['id']);
        exit_json_response($ret);
    }
    
    

    function re_send(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/SmsQueueModel')->re_send($request['id']);
        exit_json_response($ret);
    }

    function do_batch_send(array &$request, array &$response, array &$app) {
    	
        $tel_list = trim($request['tel_list']);
        $msg_content = trim($request['msg_content']);
        if (empty($tel_list) || empty($msg_content)){
            $msg = "请填写信息接收人手机号和信息内容";
			$app['fmt']='json';
	        return $response = array("status"=> -1,'message'=>$msg,'data'=>'');
        }
        $tel_arr = explode(',',$tel_list);
        $send_result = load_model('sys/SmsQueueModel')->do_batch_send($tel_arr,$msg_content);
        $this->send_msg();
        $fail_tel_arr = $send_result['fail'];
        $succ_tel_arr = $send_result['success'];
        $msg = '';
        if (!empty($succ_tel_arr)){
            $msg .= '共有'.count($succ_tel_arr).' 个手机号成功生成短信发送任务。';
        }
        if (!empty($fail_tel_arr)){
            $msg .= join(',',$fail_tel_arr).' 因手机号格式有误,无法生成短信发送任务';
        }else{
        	$msg = '恭喜您，短信发送成功！';
        }
        $app['fmt']='json';
       return  $response = array("status"=> -1,'message'=>$msg,'data'=>'');
    }

    function batch_send(array &$request, array &$response, array &$app) {

    }
   
	function send_msg(){
			
		$db_xx = load_model('sys/SmsQueueModel')->select();
//		$xx_count = count($db_xx['data']);
//		echo "\n = send_msg_count = {$xx_count} =\n";
		foreach($db_xx['data'] as $sub_xx){
			//发送短信
			$xx_id = $sub_xx['id'];
			$ret = $this->do_send($xx_id);
			if ($ret['code'] == 0)
			$status = 3;
			elseif ($ret['code'] == 1)
			$status = 1;
			else 
			$status = -1;
			$time = date('Y-m-d H:i:s', time());
			$data = array(
			'send_time' => $time,
			'status' => $status
			);
			load_model('sys/SmsQueueModel')->update($data,array('id' => $xx_id ));
		}
	} 
    
	function do_send($xx_id = null) {
		
		$xxdl_row = load_model('sys/SmsQueueModel')->query_by_id($xx_id);
		$tel = $xxdl_row['data']['tel'];
		$content = $xxdl_row['data']['msg_content'];

					$send_request = array(
                    'sms_text'=>$content,
                    'sms_phone'=>$tel,
                    'sms_priority'=>3,
                    'sms_flag'=>1,
                    'sms_template'=>2,
                    'sms_isreplay'=>0,
                );
        $send_return = $this->set_sq_msg($send_request);
        return $send_return;
    }
    
    function set_sq_msg($request){
    		$url = 'http://smsapi.sqzw.com:8080/sms/productSendAction.action?';
           	$request['sms_timersend_time'] = '';  
           	$request['sms_isrepeat'] = '0';
           	$request['app_version'] = 2;
            $request['sms_sub_account_id'] = '233_7cb7a11b76f71c24c565bb39c6';
            $request['app_key'] = 'BS01_SQZW_7_HZwSCbFtyU_6';
            $app_secret =  '12886a76b86f2f1dfab89220fef2a8a2';
            $request['app_sign'] = $this->get_sign($request,$app_secret);
            $request['sms_text'] = base64_encode(urlencode($request['sms_text']));
            foreach($request as $key=>$val){
                $url .=$key.'='.$val.'&';
            }
            $url = substr($url, 0, -1);
            return $this->curlGet($url);
        }
        
      function get_sign(&$request,$app_secret) {
      	
        ksort($request);
        $sign_text = $app_secret;

        if ($request) {
            foreach ($request as $key => $value) {
                $sign_text .= "$key$value";
            }
        }

        $sign_text .= $app_secret;
        return strtoupper(md5($sign_text));
    }
    
		function curlGet($url)
		{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				$result = curl_exec($ch);
//				echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';
				if ($result === false) {
						$err_msg = 'error: ' . curl_error($ch);
						curl_close($ch);
						return $this->put_error(-1,$err_msg);
				}
				$result = json_decode($result,true);
				curl_close($ch);
				return $result;
		}

    function upload_tel_list(array &$request, array &$response, array &$app){
        $upload_file = load_model('common/CsvImport')->get_upload("fileToUpload");
             set_uplaod($request, $response, $app);
        if ($upload_file['status'] < 0){
           // exit_json_response($upload_file);
            $response = $upload_file;
            return ;
        }
        //echo '<hr/>upload_file<xmp>'.var_export($upload_file,true).'</xmp>';
        $csv_data = load_model('common/CsvImport')->import($upload_file,null,0,100);
        if ($csv_data['status']<0){
           // exit_json_response($csv_data);
                $response = $csv_data;
            return ;
        }
        $tel_arr = array();
        foreach($csv_data['data'] as $sub_csv){
            $tel_arr[]= $sub_csv[0];
        }
        $ret = array('status'=>1,'data'=>join(',',$tel_arr));
        $response = $ret;

    }

}

