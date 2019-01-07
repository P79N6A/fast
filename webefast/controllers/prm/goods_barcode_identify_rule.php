<?php
require_lib ( 'util/web_util', true );
class goods_barcode_identify_rule {
	function do_list(array & $request, array & $response, array & $app) {
		
	}
	function detail(array & $request, array & $response, array & $app) {
		
		if(isset($request['_id']) && $request['_id'] != ''){
			
			$ret = load_model('prm/GoodsBarcodeIdentifyRuleModel')->get_by_id($request['_id']);
			if($ret['data']['rule_sort'] == '1'){
				$fangan1_arr = explode(',',$ret['data']['rule_content1']);
				$ret['data']['fangan1_length1'] = $fangan1_arr[0];
				$ret['data']['fangan1_length2'] = $fangan1_arr[1];
			}
			if($ret['data']['rule_sort'] == '2'){
				$fangan2_arr = explode('|',$ret['data']['rule_content2']);
				$fangan2_new_arr = array();
				foreach($fangan2_arr as $key => $value){
					$arr = explode(',',$value);
					$fangan2_new_arr[$key][0] = $arr[0];
					$fangan2_new_arr[$key][1] = $arr[1];
				}
				$ret['data']['fangan2'] = $fangan2_new_arr;
			}
			$response['data'] = $ret['data'];
			$response['action'] = 'do_edit';
		}else{
			$record = load_model('prm/GoodsBarcodeIdentifyRuleModel')->last_record();
			if(isset($record[0]['priority'])){
				$response['data']['priority'] = intval($record[0]['priority']) + 1;
			}else{
				$response['data']['priority'] = 1;
			}
			$response['action'] = 'do_add';
		}
		require_lib('security/CSRFHandler', true);
		$csrf_field = array('field' => CsrfHandler::TOKEN_NAME,'value'=>CsrfHandler::get_token());
		$response['data']['csrf_field'] = $csrf_field;
		//print_r($response['data']);
	}

	function do_edit(array & $request, array & $response, array & $app) {
		$rule_content2 = '';
		if($request['rule_sort'] == '2'){
			foreach ($request['fangan2_length'] as $key => $value){
				if($key%2 <> 0  ){
					$rule_content2 .= $value.'|';
				}else{
					$rule_content2 .= $value.',';
				}
			}
			$rule_content2 = substr($rule_content2,0,strlen($rule_content2)-1);
		}
		$rule_content1 = '';
		if($request['rule_sort'] == '1'){
			$rule_content1 = $request['fangan1_length1'].','.$request['fangan1_length2'];
		}
		$request['rule_content1'] = $rule_content1;
		$request['rule_content2'] = $rule_content2;
		$data = get_array_vars($request, array('rule_name','priority','rule_sort','rule_content1','rule_content2','remark'));
		//print_r($data);exit;
		$ret = load_model('prm/GoodsBarcodeIdentifyRuleModel')->update($data, $request['rule_id']);
		exit_json_response($ret);
	}

	function do_add(array & $request, array & $response, array & $app) {
		$rule_content2 = '';
		if($request['rule_sort'] == '2'){
			foreach ($request['fangan2_length'] as $key => $value){
				if($key%2 <> 0  ){
					$rule_content2 .= $value.'|';
				}else{
					$rule_content2 .= $value.',';
				}
			}
			$rule_content2 = substr($rule_content2,0,strlen($rule_content2)-1);
		}
		$rule_content1 = '';
		if($request['rule_sort'] == '1'){
			$rule_content1 = $request['fangan1_length1'].','.$request['fangan1_length2'];
		}
		$request['rule_content1'] = $rule_content1;
		$request['rule_content2'] = $rule_content2;
		$request['is_add_person'] = CTX()->get_session('user_code');
		$request['is_add_time'] = date("Y-m-d H:i:s",time());
		$data = get_array_vars($request, array('rule_name','priority','rule_sort','rule_content1','rule_content2','remark','is_add_person','is_add_time'));
		$ret = load_model('prm/GoodsBarcodeIdentifyRuleModel')->insert($data);
		exit_json_response($ret);
	}
	
	function yanzheng(array & $request, array & $response, array & $app){
		
		if(isset($request['barcode']) && $request['barcode'] <> ''){
			$ret = load_model('prm/GoodsBarcodeIdentifyRuleModel')->yanzheng($request['barcode'],'1');
			
		}
		exit_json_response($ret);
	}
	
	function do_delete(array & $request, array & $response, array & $app) {
		$ret = load_model('prm/GoodsBarcodeIdentifyRuleModel')->delete($request['rule_id']);
		exit_json_response($ret);
	}
}