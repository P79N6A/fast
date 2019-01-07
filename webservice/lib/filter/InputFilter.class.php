<?php
require_once ROOT_PATH.'boot/req_inc.php';
class InputFilter implements IRequestFilter{
	function handle_before(array & $request, array & $response, array & $app) {
		// 统一清除两边空白字符
		if (isset($request['__filter_trim']) && $request['__filter_trim'] == 'n') {
			//
		} else {
			foreach ($request as & $val) {
				$val = trim($val);
			}
		}
	}	
}